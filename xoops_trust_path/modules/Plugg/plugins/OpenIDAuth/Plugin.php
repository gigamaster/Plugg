<?php
class Plugg_OpenIDAuth_Plugin extends Plugg_Plugin implements Plugg_User_Authenticator
{
    public function userAuthGetName()
    {
        return array($this->name => $this->_('OpenID authentication'));
    }

    public function userAuthGetSettings()
    {
        return array(
            'openidRandSource' => array(
                '#type' => 'textfield',
                '#title' => $this->_('Path to random number generator'),
                '#description' => $this->_('Enter the path to a random number generator such as /dev/urandom or leave it blank to continue with an insecure random number generator.'),
                '#default_value' => $this->getConfig('openidRandSource'),
                '#size' => 50,
            ),
            'yadisCurlOptionCaInfo' => array(
                '#type' => 'textfield',
                '#title' => $this->_('Path to PEM encoded cert file'),
                '#description' => sprintf($this->_('Enter a relative path from %s/ or an absolute path starting with a %s. Leave it as-is if you are unsure about this option.'), $this->_path, DIRECTORY_SEPARATOR),
                '#default_value' => $this->getConfig('yadisCurlOptionCaInfo'),
                '#size' => 50,
            ),
        );
    }

    public function userAuthGetForm(array $defaultForm)
    {
        $form = array();
        $form['#method'] = 'get';
        $form['#id'] = 'openidauth_login';
        $styles = array(
            'background' => sprintf('url(%s) no-repeat 2px center #fff', $this->_application->ImageUrl($this->name, 'login-bg.gif', '', '&')),
            'color' => '#333',
            'padding-left' => '20px'
        );
        $styles_str = array();
        foreach ($styles as $k => $v) {
            $styles_str[] = "$k:$v";
        }
        $form['openid'] = array(
            '#title' => $this->_('OpenID'),
            '#size' => 30,
            '#maxlength' => 255,
            '#required' => true,
            '#attributes' => array('style' => implode('; ', $styles_str)),
        );
        set_include_path($this->_path . '/lib' . PATH_SEPARATOR . get_include_path());
        require_once 'Auth/OpenID/PAPE.php';
        $page_policies = array(
            PAPE_AUTH_MULTI_FACTOR_PHYSICAL => $this->_('Physical Multi-Factor Authentication'),
            PAPE_AUTH_MULTI_FACTOR => $this->_('Multi-Factor Authentication'),
            PAPE_AUTH_PHISHING_RESISTANT => $this->_('Phishing-Resistant Authentication'),
        );
        $form['policies'] = array(
            '#type' => 'checkboxes',
            '#title' => $this->_('Optionally, request these PAPE policies:'),
            '#description' => $this->_('You can optionally request one or more of these authentication policies. Leave it as it is if you are unsure.'),
            '#options' => $page_policies,
            '#collapsible' => true,
            '#collapsed' => true,
            '#default_value' => array_keys($page_policies),
        );

        // Add header
        if ($op_logos = $this->_getOpLogoList()) {
            $headers = array(
                sprintf($this->_('If you have an <a href="%s">OpenID</a> account, you can either click on one of the OpenID provider logos or enter your OpenID identifier in the form below to login.'), 'http://openid.net/')
            );
            foreach ($op_logos as $op_domain => $op_logo) {
                $op_logos_html[] = sprintf(
                    '<button type="submit" name="openid" value="%3$s"><img src="%2$s" alt="%3$s" style="margin:5px; margin-left:0;" /></button>',
                    $this->_application->getUrl('/user/login/' . $this->_name, array(
                        'openid' => $op_domain,
                        '_qf__openidauth_login' => ''
                    )),
                    $this->_application->ImageUrl($this->name, $op_logo['file'], $op_logo['dir']),
                    h($op_domain)
                );
            }
            $headers[] = implode('&nbsp;', $op_logos_html);
        } else {
            $headers = array(
                sprintf($this->_('If you have an <a href="%s">OpenID</a> account, you can use it to log in here.'), 'http://openid.net/')
            );
        }
        $form['#header'] = $headers;
        $form['#token'] = false;

        return $form;
    }

    public function userAuthSubmitForm(Plugg_Form_Form $form)
    {
        if (!defined('Auth_OpenID_RAND_SOURCE')) {
            // Check the random source validity here to prevent E_USER_ERROR
            // being triggered by the Auth_OpenID library
            if (($rand_source = $this->getConfig('openidRandSource')) &&
                ($fp = fopen($rand_source, 'r'))
            ) {
                define('Auth_OpenID_RAND_SOURCE', $rand_source);
                fclose($fp);
            } else {
                define('Auth_OpenID_RAND_SOURCE', null);
            }
        }

        // CA file curl option?
        if ($curlopt_cainfo = $this->getConfig('yadisCurlOptionCaInfo')) {
            // Prepend plugin path if not an absolute path
            $cainfo_file = strpos($curlopt_cainfo, DIRECTORY_SEPARATOR) === 0 ? $curlopt_cainfo : $this->_path . '/' . $curlopt_cainfo;
            define('Auth_Yadis_CURLOPT_CAINFO', $cainfo_file);
        }

        set_include_path($this->_path . '/lib' . PATH_SEPARATOR . get_include_path());
        require_once 'Auth/OpenID/Consumer.php';
        require_once 'Auth/OpenID/PAPE.php';
        require_once 'Auth/OpenID/SReg.php';
        switch (@$_GET['action']) {
            case 'finish':
                return $this->_userAuthFinish($form);
            default:
                $this->_userAuthTry($form);
        }
        return false;
    }

    private function _userAuthTry(Plugg_Form_Form $form)
    {
        // Begin the OpenID authentication process.
        if (!$auth_request = $this->_getConsumer()->begin($form->values['openid'])) {
            $form->setError($this->_('Authentication error; not a valid OpenID.'), 'openid');
            return;
        }

        // Add SReg
        if ($sreg_request = Auth_OpenID_SRegRequest::build(array('nickname', 'email'), array('fullname'))) {
            $auth_request->addExtension($sreg_request);
        }

        // Add PAPE
        if (!empty($form->values['policies'])) {
            $auth_request->addExtension(new Auth_OpenID_PAPE_Request($form->values['policies']));
        }

        // Redirect the user to the OpenID server for authentication.
        // For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
        // form to send a POST request to the server.
        $trust_root = $this->_application->SiteUrl();
        $return_url = $this->_getReturnUrl($form->values);
        if ($auth_request->shouldSendRedirect()) {
            $redirect_url = $auth_request->redirectURL($trust_root, $return_url);
            if (!Auth_OpenID::isFailure($redirect_url)) {
                header('Location: ' . $redirect_url);
                exit;
            }
            $form->setError(sprintf($this->_('Could not redirect to server: %s'), $redirect_url->message), 'openid');
        } else {
            // Generate form markup and render it.
            $form_html = $auth_request->htmlMarkup($trust_root, $return_url, false, array('id' => 'openid_message'));
            if (!Auth_OpenID::isFailure($form_html)) {
                print $form_html;
                exit;
            }
            $form->setError(sprintf($this->_('Could not redirect to server: %s'), $form_html->message), 'openid');
        }
    }

    private function _userAuthFinish(Plugg_Form_Form $form)
    {
        $response = $this->_getConsumer()->complete($this->_getReturnUrl($form->values));

        // Check the response status.
        switch ($response->status) {
            case Auth_OpenID_SUCCESS:
                $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
                $sreg = $sreg_resp->contents();
                return array(
                    'id' => mb_convert_encoding($response->endpoint->claimed_id, SABAI_CHARSET, 'auto'),
                    'display_id' => mb_convert_encoding($response->getDisplayIdentifier(), SABAI_CHARSET, 'auto'),
                    'username' => !empty($sreg['nickname']) ? mb_convert_encoding($sreg['nickname'], SABAI_CHARSET, 'auto') : '',
                    'email' => !empty($sreg['email']) ? $sreg['email'] : '',
                    'name' => !empty($sreg['fullname']) ? mb_convert_encoding($sreg['fullname'], SABAI_CHARSET, 'auto') : '',
                );
            case Auth_OpenID_CANCEL:
                $form->setError($this->_('Verification cancelled.'), 'openid');
                return false;
            case Auth_OpenID_FAILURE:
            default;
                $form->setError(sprintf($this->_('OpenID authentication failed: %s'), $response->message), 'openid');
                return false;
        }
    }

    private function _getConsumer()
    {
        require_once dirname(__FILE__) . '/OpenIDStore.php';
        $nonce_lifetime = !empty($GLOBALS['Auth_OpenID_SKEW']) ? $GLOBALS['Auth_OpenID_SKEW'] : 3600;
        $store = new Plugg_OpenIDAuth_OpenIDStore($this->getDB(), $nonce_lifetime);
        return new Auth_OpenID_Consumer($store);
    }

    private function _getReturnUrl($formValues)
    {
        $params = $formValues;
        $params['action'] = 'finish';
        $params[$this->_application->getRouteParam()] = '/user/login/' . $this->_name;

        return (string)$this->_application->getUrl('/user/login/' . $this->_name, $params);
    }

    private function _getOpLogoList()
    {
        $list = array();

        foreach (array($this->_path . '/op_' . SABAI_LANG, $this->_path . '/op') as $logo_dir) {
            if ($dh = @opendir($logo_dir)) {
                $logo_dirname = str_replace($this->_path . '/', '', $logo_dir);
                while (false !== ($file = readdir($dh))) {
                    if ($file == '.' || $file == '..' || (!$file_ext_pos = strrpos($file, '.'))) {
                        continue;
                    }
                    $file_ext = strtolower(substr($file, $file_ext_pos + 1));
                    if (!in_array($file_ext, array('gif', 'jpg', 'jpeg', 'png'))) {
                        continue;
                    }
                    $list[str_replace('_', '/', substr($file, 0, $file_ext_pos))] = array(
                        'dir' => $logo_dirname,
                        'file' => $file
                    );
                }
                closedir($dh);

                break;
            }
        }

        return $list;
    }
    
    public function getDefaultConfig()
    {
        return array(
            'openidRandSource' => '/dev/urandom',
            'yadisCurlOptionCaInfo' => 'certs/cacert.pem',
        );
    }
}
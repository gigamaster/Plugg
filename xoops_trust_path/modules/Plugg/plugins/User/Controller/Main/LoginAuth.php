<?php
class Plugg_User_Controller_Main_LoginAuth extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        // Check if already logged in
        if ($this->getUser()->isAuthenticated()) {
            $response->setError(null, array('base' => '/user'));

            return false;
        }

        // Get the URL to redirect
        $return_to = array();
        if ($request->asInt('return')) {
            if (!$return_to = @$request->get('return_to')) {
                $return_to = $this->getPreviousRequestUrl();
            }
        }

        // Is manager an API type plugin?
        $manager = $this->getPlugin()->getManagerPlugin();
        if ($manager instanceof Plugg_User_Manager_API) {
            $manager->userLogin($request, $response, $return_to);
            return;
        }

        // Save the return URL into session if not currently submitting the login form
        if (!empty($return_to) && !$request->isPost()) {
            $_SESSION['Plugg_User_Main_Login_return'] = $return_to;
        }

        $form = $this->auth_plugin->userAuthGetForm($this->getPlugin()->getDefaultLoginForm());
        $form['#action'] = $this->getUrl('/user/login/' . $this->auth_plugin->name);
        $form[$this->getRouteParam()] = array('#type' => 'hidden', '#value' => '/user/login/' . $this->auth_plugin->name);
        if ($this->getPlugin()->getConfig('enableAutologin')) {
            $days = $this->getPlugin()->getConfig('autologinSessionLifetime');
            $form['_autologin'] = array(
                '#type' => 'checkbox',
                '#title' => sprintf($this->_n('Remember me on this computer for 1 day', 'Remember me on this computer for %d days', $days), $days),
            );
        }
        
        $this->active_auths = $this->getPluginModel()->Auth->criteria()->active_is(1)->fetch(0, 0, 'order', 'ASC');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (($result = $this->auth_plugin->userAuthSubmitForm($form, $form->values))
            && $this->_processAuthResult($request, $response, $result, $form->values['_autologin'])
        ) {
            return true;
        }

        return false;
    }

    private function _processAuthResult(Sabai_Request $request, Sabai_Application_Response $response, $result, $autologin = false)
    {
        $claimed_id = $result['id'];
        $authdata = $this->getPluginModel()->Authdata->criteria()->claimedId_is($claimed_id)
            ->fetchByAuth($this->auth->id, 1, 0)->getFirst();
        if ($authdata) {
            $identity = $this->getPlugin()->getIdentity($authdata->user_id);
            if (!$identity->isAnonymous()) {
                if ($this->getPlugin()->loginUser($request, new Sabai_User($identity, true), $this->getPlugin()->getManagerPlugin(), $autologin)) {
                    $authdata->lastused = time();
                    $authdata->commit();

                    return true;
                }
            } else {
                // Invalid auth data
                $authdata->markRemoved();
                $authdata->commit();
            }
        }

        // Register or associate auth result with an existing user

        // Save auth data to session so that it can be passed on to register or associate with an account
        $_SESSION['Plugg_User_Main_Login_auth'] = array(
            'claimed_id' => $claimed_id,
            'display_id' => $result['display_id'],
            'username' => $result['username'],
            'email' => $result['email'],
            'name' => $result['name'],
            'auth_id' => $this->auth->id,
            'timestamp' => time(),
        );

        $settings = $this->getPlugin()->getRegisterForm($result['username'], $result['email'], $result['name']);
        $settings['#action'] = $this->getUrl('/user/register_auth');
        $settings['#header'] = array(sprintf(
            '<p>%s</p><p>%s</p>',
            $this->_('You have been authenticated successfully. However, we were unable to find a user account that is associated with the authentication.'),
            sprintf(
                $this->_('Create a new account using the registration form below or <a href="%s">associate the authentication with an existing user account</a>.'),
                $this->getUrl('/user/associate_auth', array('_autologin' => intval($autologin)))
            )
        ));
        $settings['register'] = array('#type' => 'submit', '#value' => $this->_('Register'));
        $response->setContent($this->getPlugin('Form')->buildForm($settings, true, $request->getParams())->render());

        return false;
    }
}
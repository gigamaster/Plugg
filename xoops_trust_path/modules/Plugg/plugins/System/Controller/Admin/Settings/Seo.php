<?php
class Plugg_System_Controller_Admin_Settings_Seo extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $main_url_arr = parse_url($this->createUrl(array('base' => '', 'script' => 'main', 'mod_rewrite' => false)));
        $main_url = $main_url_arr['scheme'] . '://' . $main_url_arr['host'] . $main_url_arr['path'];

        $form = array(
            'mod_rewrite' => array(
                '#type' => 'fieldset',
                '#collapsible' => true,
                '#title' => $this->_('mod_rewrite settings'),
                '#tree' => true,
                'enable' => array(
                    '#type' => 'checkbox',
                    '#default_value' => $this->getPlugin()->getConfig('mod_rewrite', 'enable'),
                    '#title' => $this->_('Enable mod_rewrite'),
                    '#description' => sprintf(
                        $this->_('Check this option to enable mod_rewrite and make URLs SEO friendly. Shown below is an example of mod_rewrite settings that you can configure in %1$s/.htaccess<br /><code>RewriteEngine on<br />RewriteCond %%{REQUEST_FILENAME} !-f<br />RewriteCond %%{REQUEST_FILENAME} !-d<br />RewriteRule ^(.+)$ %2$s/index.php?q=/$1 [L,QSA,NS]</code>'),
                        rtrim($this->SiteUrl(), '/'),
                        trim(str_replace($this->SiteUrl(), '', $main_url), '/')
                    ),
                ),
                'format' => array(
                    '#type' => 'textfield',
                    '#title' => $this->_('mod_rewrite URL format'),
                    '#description' => $this->_('If mod_rewrite is enabled, URLs will be generated in this format. You can use %1$s as the requested route (ex. /user/2), %2$s as the query string (ex. foo=bar), and %3$s as the value of %2$s prefixed with a question mark (ex. ?foo=bar).'),
                    '#default_value' => $this->getPlugin()->getConfig('mod_rewrite', 'format'),
                    '#size' => 80,
                    '#regex' => '#^(http://|https://)#i'
                )
            )
        );
        
        $this->_cancelUrl = null;
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values['mod_rewrite'])) {
            if (!$this->getPlugin()->saveConfig(array('mod_rewrite' => $form->values['mod_rewrite']))) return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}
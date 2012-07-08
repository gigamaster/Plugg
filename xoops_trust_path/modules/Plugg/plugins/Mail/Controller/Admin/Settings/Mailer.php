<?php
class Plugg_Mail_Controller_Admin_Settings_Mailer extends Plugg_Form_Controller
{
    private $_mailerPlugin;
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_mailerPlugin = $this->getPlugin($request->asStr('plugin_name'));
        $form = array(
            '#id' => 'mail_admin_settings_mailer_' . strtolower($this->_mailerPlugin->name),
            $this->_mailerPlugin->name => array_merge(
                $this->_mailerPlugin->mailGetSettings(),
                array(
                    '#type' => 'fieldset',
                    '#tree' => true,
                    '#tree_allow_override' => false,
                )
            )
        );
        
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($config = $form->values[$this->_mailerPlugin->name]) {
            if (!$this->_mailerPlugin->saveConfig($config)) return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}
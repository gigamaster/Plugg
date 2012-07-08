<?php
class Plugg_User_Controller_Admin_Settings_Manager extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            '#id' => 'user_admin_settings_manager_' . strtolower($this->manager_plugin->name),
            $this->manager_plugin->name => array_merge(
                $this->manager_plugin->userGetManagerSettings(),
                array(
                    '#type' => 'fieldset',
                    '#tree' => true,
                )
            )
        );
        
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values[$this->manager_plugin->name])) {
            if (!$this->manager_plugin->saveConfig($form->values[$this->manager_plugin->name])) {
                return false;
            }
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}
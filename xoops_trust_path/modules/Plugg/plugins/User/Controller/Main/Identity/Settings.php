<?php
class Plugg_User_Controller_Main_Identity_Settings extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form['settings'] = array(
            '#tree' => true,
            '#tree_allow_override' => false,
        );
        foreach ($this->getPluginManager()->getInstalledPluginsByInterface('Plugg_User_AccountSetting') as $plugin_name => $plugin_nicename) {
            if (!$plugin = $this->getPlugin($plugin_name)) continue;
            
            foreach ($plugin->userAccountSettingGetNames() as $setting_name) {
                $field_name = strtolower($plugin_name) . '_' . $setting_name;
                $form['settings'][$field_name] = $plugin->userAccountSettingGetSettings($setting_name, $this->identity);
                if ($this->identity->hasData($field_name)) {
                    $form['settings'][$field_name]['#default_value'] = $this->identity->getData($field_name);
                }
            }
        }
        
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['settings'])) return true;
        
        return $this->User_IdentitySaveMeta($this->identity, $form->values['settings']);
    }
}
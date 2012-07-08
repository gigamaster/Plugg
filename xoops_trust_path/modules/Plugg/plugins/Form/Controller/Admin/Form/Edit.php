<?php
class Plugg_Form_Controller_Admin_Form_Edit extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {   
        $this->_submitButtonLabel = $this->_('Save configuration');
        
        return $this->getPluginModel()->createForm($this->form);
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->form->title = $form->values['title'];
        $this->form->header = $form->values['header']['text'];
        $this->form->header_formatted = $form->values['header']['filtered_text'];
        $this->form->header_format = $form->values['header']['filter_id'];
        $this->form->hidden = $form->values['hidden'];
        $this->form->weight = $form->values['weight'];
        $this->form->submit_button_label = $form->values['submit_button_label'];
        $this->form->confirm = $form->values['confirm'];
        $this->form->confirm_button_label = $form->values['confirm_button_label'];
        
        // Save access settings
        $this->form->setAnonymousAccess(array('view', 'submit'), false); // reset anonymous user access settings
        $role_access = array();
        if (!empty($form->values['access'])) {
            foreach ($form->values['access'] as $access_type => $access_roles) {
                foreach ($access_roles as $role_id) {
                    if ($role_id == 0) {
                        $this->form->setAnonymousAccess($access_type, true);
                    } else {
                        $role_access[$role_id][] = $access_type;
                    }
                }        
            }
        }
        
        if (!$this->form->commit()) return false;
        
        // Save role access permissions
        $user_model = $this->getPlugin('User')->getModel();
        foreach ($user_model->Role->fetch() as $role) {
            $role->resetPermissions('Form');
            if (!empty($role_access[$role->id])) {
                $permissions = array();
                foreach ($role_access[$role->id] as $access_type) {
                    $permissions[] = sprintf('Form %s form %d', $access_type, $this->form->id);
                }
                $role->addPermission('Form', $permissions);
            }
        }
        $user_model->commit();
        
        return true;
    }
}
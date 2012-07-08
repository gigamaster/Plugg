<?php
class Plugg_Form_Controller_Admin_AddForm extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = $this->getPluginModel()->createForm('Form');
        $form['display']['#collapsed'] = $form['access']['#collapsed'] = false;
        $this->_submitButtonLabel = $this->_('Add form');
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $model = $this->getPluginModel();
        
        $new_form = $model->create('Form');
        $new_form->markNew();
        $new_form->title = $form->values['title'];
        $new_form->header = $form->values['header']['text'];
        $new_form->header_formatted = $form->values['header']['filtered_text'];
        $new_form->header_format = @$form->values['header']['filter_id'];
        $new_form->hidden = $form->values['hidden'];
        $new_form->weight = $form->values['weight'];
        $new_form->setEmailSettings(array());
        $new_form->submit_button_label = $form->values['submit_button_label'];
        $new_form->confirm = $form->values['confirm'];
        $new_form->confirm_button_label = $form->values['confirm_button_label'];
        
        // Save access settings
        $role_access = array();
        if (!empty($form->values['access'])) {
            foreach ($form->values['access'] as $access_type => $access_roles) {
                foreach ($access_roles as $role_id) {
                    if ($role_id == 0) {
                        $new_form->setAnonymousAccess($access_type, true);
                    } else {
                        $role_access[$role_id][] = $access_type;
                    }
                }        
            }
        }
        
        // Create the default fieldset for this form
        if (!$this->getPlugin()->createDefaultFieldset($new_form)) return false;
        
        if (!$model->commit()) return false;
        
        // Save role access permissions
        if (!empty($role_access)) {
            $user_model = $this->getPlugin('User')->getModel();
            $roles = $user_model->Role->criteria()->id_in(array_keys($role_access))->fetch();
            foreach ($roles as $role) {
                $permissions = array();
                foreach ($role_access[$role->id] as $access_type) {
                   $permissions[] = sprintf('Form %s form %d', $access_type, $new_form->id);
                }
                $role->addPermission('Form', $permissions);
            }
            $user_model->commit();
        }
        
        $response->setSuccess(
            $this->_('Form created successfully.'),
            $this->getUrl('/content/form/' . $new_form->id . '/edit/fields')
        );
        
        return true;
    }
}
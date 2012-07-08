<?php
class Plugg_User_Controller_Admin_Roles_AddRole extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = $this->getPluginModel()->createForm('Role', $this->getPlugin()->getPermissions());
        
        $this->_submitButtonLabel = $this->_('Add role');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $role = $this->getPluginModel()->create('Role');
        $role->set($form->values);
        $role->setPermissions($form->values['_permissions']);
        $role->markNew();

        if (!$this->getPluginModel()->commit()) return false;

        $response->setSuccess(
            $this->_('A new role created successfully.'),
            $this->getPlugin()->getUrl('roles/' . $role->id)
        );
    }
}
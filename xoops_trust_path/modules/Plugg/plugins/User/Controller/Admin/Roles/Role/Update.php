<?php
class Plugg_User_Controller_Admin_Roles_Role_Update extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = $this->getPluginModel()->createForm(
            $this->role,
            $this->getPlugin()->getPermissions()
        );
        $this->_submitButtonLabel = $this->_('Save changes');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->role->set($form->values);
        $this->role->setPermissions($form->values['_permissions']);

        return $this->getPluginModel()->commit();
    }
}
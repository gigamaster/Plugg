<?php
class Plugg_User_Controller_Admin_Roles_Role extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $permissions = $this->getPlugin()->getPermissions();
 
        $response->setContent($this->RenderTemplate('user_admin_roles_role', array('permissions' => $permissions[0])));
    }
}
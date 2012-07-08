<?php
class Plugg_Groups_Controller_Admin_Group extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $response->setContent($this->RenderTemplate('groups_admin_group'));
    }
}
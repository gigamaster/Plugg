<?php
class Plugg_Form_Controller_Admin_Form extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $response->setContent($this->RenderTemplate('form_admin_form'));
    }
}

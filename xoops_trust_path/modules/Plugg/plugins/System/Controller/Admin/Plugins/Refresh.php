<?php
class Plugg_System_Controller_Admin_Plugins_Refresh extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $request->set('refresh', 1);
        $this->forward('/system/plugins', $request, $response);
    }
}
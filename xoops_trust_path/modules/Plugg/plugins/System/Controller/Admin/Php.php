<?php
class Plugg_System_Controller_Admin_Php extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        ob_start();
        phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_VARIABLES);
        $response->setContent(ob_get_clean())->setLayoutEnabled(false)->setNavigationEnabled(false);
    }
}
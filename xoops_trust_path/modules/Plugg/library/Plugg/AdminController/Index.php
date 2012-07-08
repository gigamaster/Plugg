<?php
class Plugg_AdminController_Index extends Sabai_Application_Controller
{
    private static $_done = false;

    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Prevent recursive routing
        if (!self::$_done) {
            $this->forward('/adminwidget', $request, $response);
            self::$_done = true;
        }
    }
}
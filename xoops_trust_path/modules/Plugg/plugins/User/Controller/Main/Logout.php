<?php
class Plugg_User_Controller_Main_Logout extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->forward(sprintf('%d/logout/%s', $this->getUser()->id, $this->getNextRoute()), $request, $response);
    }
}
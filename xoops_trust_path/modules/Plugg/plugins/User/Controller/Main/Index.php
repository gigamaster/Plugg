<?php
class Plugg_User_Controller_Main_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($this->getUser()->isAuthenticated()) {
            $this->forward(sprintf('%d/%s', $this->getUser()->id, $this->getNextRoute()), $request, $response);
            return;
        }
        $this->forward('/user/login', $request, $response);
    }
}
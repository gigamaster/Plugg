<?php
class Plugg_Messages_Controller_Main_User_SendMessage extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $request->set('to', $this->identity->username);
        $this->forward('/user/' . $this->getUser()->id . '/messages/new', $request, $response);
    }
}
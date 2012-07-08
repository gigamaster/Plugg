<?php
class Plugg_Friends_Controller_Main_User_Requests extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {   
        $response->setContent($this->RenderTemplate('friends_main_user_requests'));
    }
}
<?php
class Plugg_User_Controller_Main_Identity_Status extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (($status = $this->getPluginModel()->Status->fetchByUser($this->identity->id)->getFirst())
            && $status->text_filtered
        ) {
            $response->setContent($status->text_filtered);
        } else {
            $response->setContent('<p>...</p>');
        }
    }
}
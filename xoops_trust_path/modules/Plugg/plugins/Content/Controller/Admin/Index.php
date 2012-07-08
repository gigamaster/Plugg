<?php
class Plugg_Content_Controller_Admin_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $response->setContent($this->_('This section is a placeholder for the forthcoming content creation feature as known as CCK.'));
    }
}
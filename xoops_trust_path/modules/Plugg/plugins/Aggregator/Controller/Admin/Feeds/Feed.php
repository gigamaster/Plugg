<?php
class Plugg_Aggregator_Controller_Admin_Feeds_Feed extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $response->setContent($this->RenderTemplate('aggregator_admin_feeds_feed'));
    }
}
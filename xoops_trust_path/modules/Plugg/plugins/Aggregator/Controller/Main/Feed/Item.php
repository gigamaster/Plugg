<?php
class Plugg_Aggregator_Controller_Main_Feed_Item extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $response->setContent($this->RenderTemplate('aggregator_main_feed_item'));
    }
}
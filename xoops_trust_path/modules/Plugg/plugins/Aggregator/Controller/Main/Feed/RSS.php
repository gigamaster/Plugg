<?php
class Plugg_Aggregator_Controller_Main_Feed_RSS extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->items = $this->getPluginModel()->Item
            ->criteria()
            ->hidden_is(0)
            ->fetchByFeed($this->feed->id, 20, 0, 'item_published', 'DESC');
        $response->isFeed();
    }
}
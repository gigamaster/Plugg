<?php
class Plugg_Aggregator_Controller_Main_User_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $pages = $this->getPluginModel()->Feed
            ->criteria()
            ->status_is(Plugg_Aggregator_Plugin::FEED_STATUS_APPROVED)
            ->paginateByUser($this->identity->id, 10, 'feed_name', 'ASC');
        $page = $pages->getValidPage($request->asInt('p', 1));
        
        $vars = array(
            'feeds' => $page->getElements()->with('User')->with('LastItem'),
            'pages' => $pages,
            'page' => $page,
        );

        $response->setContent($this->RenderTemplate('aggregator_main_user_index', $vars));
    }
}
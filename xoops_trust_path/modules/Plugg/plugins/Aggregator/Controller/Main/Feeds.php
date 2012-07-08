<?php
class Plugg_Aggregator_Controller_Main_Feeds extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $sortby_allowed = array(
            'created,DESC' => $this->_('Newest first'),
            'created,ASC' => $this->_('Oldest first'),
        );
        $sortby = $request->asStr('sortby', 'created,DESC', array_keys($sortby_allowed));
        $sortby_parts = explode(',', $sortby);
        $pages = $this->getPluginModel()->Feed
            ->criteria()
            ->status_is(Plugg_Aggregator_Plugin::FEED_STATUS_APPROVED)
            ->paginate(10, $sortby_parts[0], $sortby_parts[1]);
        $page = $pages->getValidPage($request->asInt('p', 1));
        
        $vars = array(
            'feeds' => $page->getElements()->with('User')->with('LastItem'),
            'pages' => $pages,
            'page' => $page,
            'sortby' => $sortby,
            'sortby_allowed' => $sortby_allowed,
        );

        $response->setContent($this->RenderTemplate('aggregator_main_feeds', $vars));
    }
}
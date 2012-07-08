<?php
class Plugg_Aggregator_Controller_Main_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $items_sortby_allowed = array(
            'published,DESC' => $this->_('Newest first'),
            'published,ASC' => $this->_('Oldest first'),
        );
        $items_sortby = $request->asStr('sortby', 'published,DESC', array_keys($items_sortby_allowed));

        $sortby = explode(',', $items_sortby);
        $pages = $this->getPluginModel()->Item
            ->criteria()
            ->hidden_is(0)
            ->paginate(20, $sortby[0], $sortby[1]);
        $page = $pages->getValidPage($request->asInt('p', 1));
        
        $vars = array(
            'items' => $page->getElements()->with('Feed'),
            'pages' => $pages,
            'page' => $page,
            'sortby' => $items_sortby,
            'sortby_allowed' => $items_sortby_allowed,
        );

        $response->setContent($this->RenderTemplate('aggregator_main_index', $vars));
    }
}
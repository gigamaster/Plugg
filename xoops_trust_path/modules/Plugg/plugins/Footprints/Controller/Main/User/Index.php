<?php
class Plugg_Footprints_Controller_Main_User_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $sortby_allowed = array(
            'timestamp,DESC' => $this->_('Newest first'),
            'timestamp,ASC' => $this->_('Oldest first'),
        );
        $sortby_requested = $request->asStr('sortby', 'timestamp,DESC', array_keys($sortby_allowed));

        $sortby = explode(',', $sortby_requested);
        $pages = $this->getPluginModel()->Footprint
            ->criteria()
            ->hidden_is(0)
            ->target_is($this->identity->id)
            ->paginate(20, $sortby[0], $sortby[1]);
        $page = $pages->getValidPage($request->asInt('p', 1));
        
        $vars = array(
            'footprints' => $page->getElements()->with('User'),
            'pages' => $pages,
            'page' => $page,
            'sortby' => $sortby_requested,
            'sortby_allowed' => $sortby_allowed,
        );

        $response->setContent($this->RenderTemplate('footprints_main_user_index', $vars));
    }
}
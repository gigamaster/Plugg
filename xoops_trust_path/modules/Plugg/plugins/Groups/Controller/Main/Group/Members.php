<?php
class Plugg_Groups_Controller_Main_Group_Members extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {   
        $vars = array(
            'member_pages' => $pages = $this->getPluginModel()->Member
                ->criteria()
                ->status_is(Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE)
                ->paginateByGroup($this->group->id, 50, 'created', 'DESC'),
            'member_page' => $page = $pages->getValidPage($request->asInt('p', 1)),
            'members' => $page->getElements()->with('User'),
        );
        
        $response->setContent($this->RenderTemplate('groups_main_group_members', $vars));
    }
}
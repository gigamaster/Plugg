<?php
class Plugg_Groups_Controller_Main_User_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $vars = array(
            'member_pages' => $pages = $this->getPluginModel()->Member->paginateByUser($this->identity->id, 50, 'created', 'DESC'),
            'member_page' => $page = $pages->getValidPage($request->asInt('p', 1)),
            'members' => $page->getElements()->with('Group', 'MemberCount'),
        );
        
        $response->setContent($this->RenderTemplate('groups_main_user_index', $vars));
    }
}
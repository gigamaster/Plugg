<?php
class Plugg_Friends_Controller_Main_User_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $vars = array(
            'friend_pages' => $friend_pages = $this->getPluginModel()->Friend->paginateByUser($this->identity->id, 50, 'created', 'DESC'),
            'friend_page' => $friend_page = $friend_pages->getValidPage($request->asInt('p', 1)),
            'friends' => $friend_page->getElements()->with('WithUser'),
        );
        
        $response->setContent($this->RenderTemplate('friends_main_user_index', $vars));
    }
}
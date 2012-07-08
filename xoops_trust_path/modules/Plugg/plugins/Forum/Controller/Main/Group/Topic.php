<?php
class Plugg_Forum_Controller_Main_Group_Topic extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!$this->topic->isOwnedBy($this->getUser())) {
            $this->topic->views = $this->topic->views + 1;
            $this->topic->commit();
        }
        if ($this->getUser()->isAuthenticated()) $this->_createOrUpdateTopicView();

        $vars = array(
            'comment_pages' => $pages = $this->getPluginModel()->Comment
                ->paginateByTopic($this->topic->id, 20, 'created', 'ASC'),
            'comment_page' => $page = $pages->getValidPage($request->asInt('p', 1)),
            'comments' => $page->getElements()->with('User')->with('Attachments'),
        );
        
        $response->setContent($this->RenderTemplate('forum_main_group_topic', $vars));
    }
    
    private function _createOrUpdateTopicView()
    {
        $view = $this->getPluginModel()->View
            ->criteria()
            ->userId_is($this->getUser()->id)
            ->fetchByTopic($this->topic->id)
            ->getFirst();
        if (!$view) {
            $view = $this->topic->createView();
            $view->assignUser($this->getUser());
            $view->markNew();
        }
        $view->last_viewed = time();
        $view->commit();
    }
}
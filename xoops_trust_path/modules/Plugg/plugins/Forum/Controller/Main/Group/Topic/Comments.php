<?php
class Plugg_Forum_Controller_Main_Group_Topic_Comments extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $vars = array(
            'comment_pages' => $pages = $this->getPluginModel()->Comment
                ->paginateByTopic($this->topic->id, 20, 'created', 'ASC'),
            'comment_page' => $page = $pages->getValidPage($request->asInt('p', 1)),
            'comments' => $page->getElements()->with('User')->with('Attachments'),
        );
        
        $response->setContent($this->RenderTemplate('forum_main_group_topic_comments', $vars));
    }
}
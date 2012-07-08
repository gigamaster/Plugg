<?php
class Plugg_Forum_Controller_Main_Group_Topic_Comment_Content extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $meta_html = array(
            sprintf(
                '<a href="%1$s" title="%2$s">#%3$d-%4$d</a>',
                $this->group->getUrl('forum/' . $this->comment->topic_id . '/' . $this->comment->id),
                $this->_('Permalink'),
                $this->comment->topic_id,
                $this->comment->id
            )
        );
        if ($this->comment->parent) {
            $meta_html[] = sprintf(
                $this->_('Posted in reply to <a href="%1$s">#%2$d-%3$d</a>'),
                $this->group->getUrl('forum/' . $this->comment->topic_id . '/' . $this->comment->parent),
                $this->comment->topic_id,
                $this->comment->parent
            );
        }

        $response->setContent($this->RenderTemplate('plugg_list_item_content', array('item' => array(
            'id' => 'plugg-forum-comment' . $this->comment->id,
            'title' => $this->comment->title,
            'timestamp' => $this->comment->created,
            'user' => $this->comment->User,
            'meta_html' => $meta_html,
            'body_html' => $this->comment->body_html,
            'attachments' => ($file_ids = $this->comment->getAttachmentFileIds()) ? $this->Uploads_FileList($file_ids) : array(),
        ))));
    }
}
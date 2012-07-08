<?php
class Plugg_Forum_Controller_Main_Group_Topic_Content extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $response->setContent($this->RenderTemplate('plugg_list_item_content', array('item' => array(
            'id' => 'plugg-forum-topic' . $this->topic->id,
            'title' => $this->topic->getTitle(),
            'timestamp' => $this->topic->created,
            'user' => $this->topic->User,
            'meta_html' => array(
                sprintf(
                    '<a href="%1$s" title="%2$s">#%3$d</a>',
                    $this->group->getUrl('forum/' . $this->topic->id),
                    $this->_('Permalink'),
                    $this->topic->id
                ),
            ),
            'body_html' => $this->topic->body_html,
            'attachments' => ($file_ids = $this->topic->getAttachmentFileIds()) ? $this->Uploads_FileList($file_ids) : array(),
        ))));
    }
}
<?php
class Plugg_Forum_Controller_Main_Group_Topic_AddComment extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$this->getUser()->isAuthenticated()) {
            $response->setLoginRequiredError();

            return false;
        }

        if (!$this->getUser()->hasPermission('forum comment')) {
            $response->setError($this->_('Permission denied'));

            return false;
        }

        $this->_submitButtonLabel = $this->_('Post reply');
        //$this->_ajaxSubmit = false;
        $this->_cancelUrl = $this->getUrl('/groups/' . $this->group->name . '/forum/' . $this->topic->id);

        $form = $this->getPluginModel()->createForm('Comment');
        if (!$this->getUser()->hasPermission('forum attach file')) {
            unset($form['attachments']);
        }

        // Add quoted text only if the topic body is not empty
        if ($this->topic->body) {
            $form['body']['#default_value']['text'] = "\n\n" . strtr("\n" . $this->topic->body, array("\n>" => "\n>>", "\n" => "\n> "));
        }

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $comment = $this->getPluginModel()->create('Comment');
        $comment->markNew();
        $comment->title = $form->values['title'];
        $comment->body = $form->values['body']['text'];
        $comment->body_html = $form->values['body']['filtered_text'];
        $comment->body_filter_id = $form->values['body']['filter_id']; 
        $comment->assignUser($this->getUser());
        $comment->assignTopic($this->topic);
        $comment->ip = getip();
        $comment->host = gethostbyaddr($comment->ip);
        $this->topic->last_posted = time();
        
        if (!empty($form->values['attachments'])) {
            foreach ($form->values['attachments'] as $file_id) { 
                $attachment = $this->topic->createAttachment();
                $attachment->file_id = $file_id;
                $attachment->assignComment($comment);
                $attachment->markNew();
            }
        }

        if ($this->getPluginModel()->commit()) {
            $this->DispatchEvent('ForumCommentSubmitSuccess', array($comment, /*$isEdit*/ false));
            $response->setSuccess(
                $this->_('Comment submitted successfully.'),
                $this->getUrl('/groups/' . $this->group->name . '/forum/' . $this->topic->id . '/' . $comment->id)
            );

            return true;
        }

        return false;
    }
}
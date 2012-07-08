<?php
class Plugg_Forum_Controller_Main_Group_Topic_Comment_Edit extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$this->getUser()->hasPermission('forum comment edit any')) {
            if (!$this->comment->isOwnedBy($this->getUser())
                || !$this->getUser()->hasPermission('forum comment edit own')
            ) {
                $response->setError($this->_('Permission denied'));

                return false;
            }
        }

        $this->_submitButtonLabel = $this->_('Save');

        $path = '/groups/' . $this->group->name . '/forum/' . $this->topic->id . '/' . $this->comment->id;
        $this->_cancelUrl = $this->getUrl($path);

        $this->_ajaxCancelType = 'remote';
        $this->_ajaxCancelUrl = $this->_ajaxOnSuccessUrl = $this->getUrl($path . '/content');

        $form = $this->getPluginModel()->createForm($this->comment);

        if (!$this->getUser()->hasPermission('forum attach file')) {
            unset($form['attachments']);
        }
        if ($this->comment->title) {
            $form['_title']['#title'] = $this->_('Title');
            $form['_title']['#collapsed'] = false;
        }

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->comment->title = $form->values['title'];
        $this->comment->body = $form->values['body']['text'];
        $this->comment->body_html = $form->values['body']['filtered_text'];
        $this->comment->body_filter_id = $form->values['body']['filter_id']; 
        
        $file_ids = !empty($form->values['attachments']) ? $form->values['attachments'] : array();
        if ($this->comment->Attachments->count()) {
            foreach ($this->comment->Attachments as $attachment) {
                if (!in_array($attachment->file_id, $file_ids)) {
                    $attachment->markRemoved(); // not selected
                } else {
                    unset($file_ids[$attachment->file_id]);
                }
            } 
        }
        // New attachments
        foreach ($file_ids as $file_id) {
            $attachment = $this->topic->createAttachment();
            $attachment->assignComment($this->comment);
            $attachment->file_id = $file_id;
            $attachment->markNew();
        }
        
        if ($this->getPluginModel()->commit()) {
            $this->DispatchEvent('ForumCommentSubmitSuccess', array($this->comment, /*$isEdit*/ true));
            $response->setSuccess(
                $this->_('Comment updated successfully.'),
                $this->_cancelUrl
            );

            return true;
        }

        return false;
    }
}
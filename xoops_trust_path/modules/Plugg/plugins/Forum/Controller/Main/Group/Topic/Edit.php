<?php
class Plugg_Forum_Controller_Main_Group_Topic_Edit extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$this->getUser()->hasPermission('forum topic edit any')) {
            if (!$this->topic->isOwnedBy($this->getUser())
                || !$this->getUser()->hasPermission('forum topic edit own')
            ) {
                $response->setError($this->_('Permission denied'));

                return false;
            }
        }

        $this->_submitButtonLabel = $this->_('Save');

        $path = '/groups/' . $this->group->name . '/forum/' . $this->topic->id;
        $this->_cancelUrl = $this->getUrl($path);
        $this->_ajaxCancelType = 'remote';
        $this->_ajaxCancelUrl = $this->_ajaxOnSuccessUrl = $this->getUrl($path . '/content');

        $form = $this->getPluginModel()->createForm($this->topic);
        if (!$this->getUser()->hasPermission('forum attach file')) {
            unset($form['attachments']);
        }
        if (!$this->getUser()->hasPermission('forum topic sticky')) {
            unset($form['options']['sticky']);
        }
        if ($this->topic->isOwnedBy($this->getUser())) {
            if (!$this->getUser()->hasPermission(array('forum topic close any', 'forum topic close own'))) {
                unset($form['options']['closed']);
            }
        } else {
            if (!$this->getUser()->hasPermission('forum topic close any')) {
                unset($form['options']['closed']);
            }
        }
        if (!isset($form['options']['sticky']) && !isset($form['options']['closed'])) unset($form['options']);

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->topic->title = $form->values['title'];
        $this->topic->body = $form->values['body']['text'];
        $this->topic->body_html = $form->values['body']['filtered_text'];
        $this->topic->body_filter_id = $form->values['body']['filter_id'];
        $this->topic->closed = !empty($form->values['closed']);
        $this->topic->sticky = !empty($form->values['sticky']);
        
        $file_ids = !empty($form->values['attachments']) ? $form->values['attachments'] : array();
        if ($this->topic->Attachments->count()) {
            foreach ($this->topic->getAttachments() as $attachment) {
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
            $attachment->file_id = $file_id;
            $attachment->markNew();
        }
        
        if ($this->getPluginModel()->commit()) {
            $this->DispatchEvent('ForumTopicSubmitSuccess', array($this->topic, /*$isEdit*/ true));
            $response->setSuccess(
                $this->_('Topic updated successfully.'),
                $this->_cancelUrl
            );

            return true;
        }

        return false;
    }
}
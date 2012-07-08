<?php
class Plugg_Forum_Controller_Main_Group_NewTopic extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$this->getUser()->isAuthenticated()) {
            $response->setLoginRequiredError();
            
            return false;
        }

        if (!$this->getUser()->hasPermission('forum topic post')) {
            $response->setError($this->_('Permission denied'));
            
            return false;
        }

        $this->_cancelUrl = array();
        $this->_ajaxCancelType = 'none';
        $this->_ajaxOnSuccess = sprintf('function(xhr, result, target){target.hide();}');
        $this->_submitButtonLabel = $this->_('Submit new topic');

        $form = $this->getPluginModel()->createForm('Topic');
        if (!$this->getUser()->hasPermission('forum attach file')) {
            unset($form['attachments']);
        }
        if (!$this->getUser()->hasPermission('forum topic sticky')) {
            unset($form['options']['sticky']);
        }
        if (!$this->getUser()->hasPermission(array('forum topic close any', 'forum topic close own'))) {
            unset($form['options']['closed']);
        }
        if (!isset($form['options']['sticky']) && !isset($form['options']['closed'])) unset($form['options']);

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $topic = $this->getPluginModel()->create('Topic');
        $topic->title = $form->values['title'];
        $topic->body = $form->values['body']['text'];
        $topic->body_html = $form->values['body']['filtered_text'];
        $topic->body_filter_id = $form->values['body']['filter_id'];
        $topic->closed = !empty($form->values['closed']);
        $topic->sticky = !empty($form->values['sticky']);
        $topic->assignUser($this->getUser());
        $topic->assignGroup($this->group);
        $topic->ip = getip();
        $topic->host = gethostbyaddr($topic->ip);
        $topic->last_posted = time();
        $topic->views = 1;
        $topic->markNew();
        
        if (!empty($form->values['attachments'])) {
            foreach ($form->values['attachments'] as $file_id) { 
                $attachment = $topic->createAttachment();
                $attachment->file_id = $file_id;
                $attachment->markNew();
            }
        }

        if ($this->getPluginModel()->commit()) {
            $this->DispatchEvent('ForumTopicSubmitSuccess', array($topic, /*$isEdit*/ false));
            $response->setSuccess(
                $this->_('Topic created successfully.'),
                $this->getUrl('/groups/' . $this->group->name . '/forum/' . $topic->id)
            );

            return true;
        }

        return false;
    }
}
<?php
class Plugg_Forum_Controller_Main_Group_Topic_Delete extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$this->getUser()->hasPermission('forum topic delete any')) {
            if (!$this->topic->isOwnedBy($this->getUser())
                || !$this->getUser()->hasPermission('forum topic delete own')
            ) {
                $response->setError($this->_('Permission denied'));

                return false;
            }
        }

        $this->_submitButtonLabel = $this->_('Yes, delete');
        $path = '/groups/' . $this->group->name . '/forum/' . $this->topic->id;
        $this->_cancelUrl = $this->getUrl($path);
        $this->_ajaxCancelType = 'remote';
        $this->_ajaxCancelUrl = $this->getUrl($path . '/content');

        if ($request->asInt('inline')) {
            $this->_ajaxOnSuccess = sprintf(
                'function(){jQuery("#plugg-forum-topic%d").fadeTo("fast", 0, function(){jQuery(this).slideUp("medium");});}',
                $this->topic->id
            );
            $this->_ajaxOnSuccessRedirect = false;
        }

        $form['#header'][] = sprintf(
            '<div class="plugg-warning">%s</div>',
            $this->_('Are you sure you want to delete this topic and all its comments?')
        );
        $form['topic'] = array(
            '#type' => 'item',
            '#title' => $this->_('Topic'),
            '#markup' => $this->topic->body_html,
        );

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->topic->markRemoved();
        if ($this->getPluginModel()->commit()) {
            $this->DispatchEvent('ForumTopicDeleteSuccess', array($this->topic));
            $response->setSuccess(
                $this->_('Topic deleted successfully.'),
                $this->getUrl('/groups/' . $this->group->name . '/forum')
            );

            return true;
        }

        return false;
    }
}
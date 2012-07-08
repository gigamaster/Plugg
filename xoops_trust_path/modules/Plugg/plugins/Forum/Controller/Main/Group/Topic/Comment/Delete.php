<?php
class Plugg_Forum_Controller_Main_Group_Topic_Comment_Delete extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$this->getUser()->hasPermission('forum comment delete any')) {
            if (!$this->comment->isOwnedBy($this->getUser())
                || !$this->getUser()->hasPermission('forum comment delete own')
            ) {
                $response->setError($this->_('Permission denied'));

                return false;
            }
        }

        $this->_submitButtonLabel = $this->_('Yes, delete');

        $path = '/groups/' . $this->group->name . '/forum/' . $this->topic->id . '/' . $this->comment->id;
        $this->_cancelUrl = $this->getUrl($path);
        
        $this->_ajaxCancelType = 'remote';
        $this->_ajaxCancelUrl = $this->getUrl($path . '/content');
        $this->_ajaxOnSuccess = sprintf('function(){jQuery("#plugg-forum-comment%d").fadeTo("fast", 0, function(){jQuery(this).slideUp("medium");});}', $this->comment->id);
        $this->_ajaxOnSuccessRedirect = false;

        $form['#header'][] = sprintf(
            '<div class="plugg-warning">%s</div>',
            $this->_('Are you sure you want to delete this comment?')
        );
        $form['comment'] = array(
            '#type' => 'item',
            '#title' => $this->_('Comment'),
            '#markup' => $this->comment->body_html,
        );

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->comment->markRemoved();

        $model = $this->getPluginModel();
        if ($model->commit()) {
            // Set the parent id of child comments to parent of this comment
            //$model->getGateway('Comment')->updateByCriteria(
            //    $model->createCriteria('Comment')->parentId_is($this->comment->id),
            //    array('comment_parent_id' => $this->comment->parent_id)
            //);

            $this->DispatchEvent('ForumCommentDeleteSuccess', array($this->comment));
            $response->setSuccess(
                $this->_('Comment deleted successfully.'),
                $this->getUrl('/groups/' . $this->group->name . '/forum/' . $this->topic->id)
            );

            return true;
        }

        return false;
    }
}
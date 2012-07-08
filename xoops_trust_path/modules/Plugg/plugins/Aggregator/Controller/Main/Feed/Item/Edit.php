<?php
class Plugg_Aggregator_Controller_Main_Feed_Item_Edit extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$this->getUser()->hasPermission('aggregator item edit any')) {
            if (!$this->feed_item->isOwnedBy($this->getUser())
                || !$this->getUser()->hasPermission('aggregator item edit own')
            ) {
                return false;
            }
        }

        $this->_cancelUrl = $this->_successUrl = $this->getPlugin()->getUrl($this->feed->id . '/' . $this->feed_item->id);

        $form = $this->getPluginModel()->createForm($this->feed_item);
        unset($form['url']);

        $user = $this->getUser();
        if (!$user->isSuperUser()) {
            if ($this->feed->isOwnedBy($user)) {
                if (!$user->hasPermission(array('aggregator item edit own body', 'aggregator item edit any body'))) {
                    unset($form['body']);
                }
                if (!$user->hasPermission(array('aggregator item edit own author', 'aggregator item edit any author'))) {
                    unset($form['author']);
                }
                if (!$user->hasPermission(array('aggregator item edit own author link', 'aggregator item edit any author link'))) {
                    unset($form['author_link']);
                }
                if (!$user->hasPermission(array('aggregator item hide own', 'aggregator item hide any'))) {
                    unset($form['hidden']);
                }
            } else {
                if (!$user->hasPermission(array('aggregator item edit any body'))) {
                    unset($form['body']);
                }
                if (!$user->hasPermission(array('aggregator item edit any author'))) {
                    unset($form['author']);
                }
                if (!$user->hasPermission(array('aggregator item edit any author link'))) {
                    unset($form['author_link']);
                }
                if (!$user->hasPermission(array('aggregator item hide any'))) {
                    unset($form['hidden']);
                }
            }
        }

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        foreach (array('title', 'body', 'author', 'author_link', 'hidden') as $key) {
            if (!isset($form->settings[$key])) continue;
            
            $this->feed_item->$key = $form->values[$key];
        }

        // Purify item body if body element is active
        if (!empty($this->feed_item->body)) {
            $this->feed_item->body = $this->getPlugin()->getHTMLPurifier($this->feed)->purify($this->feed_item->body);
        }

        if (!$this->feed_item->commit()) return false;

        $this->feed->updateLastPublished();

        return true;
    }
}
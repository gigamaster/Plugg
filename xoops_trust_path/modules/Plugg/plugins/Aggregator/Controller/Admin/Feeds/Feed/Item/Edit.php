<?php
class Plugg_Aggregator_Controller_Admin_Feeds_Feed_Item_Edit extends Plugg_Form_Controller
{   
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_cancelUrl = $this->_successUrl = $this->getUrl('/content/aggregator/feeds/' . $this->feed->id);
        
        return $this->getPluginModel()->createForm($this->feed_item);
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->feed_item->title = $form->values['title'];
        $this->feed_item->url = $form->values['url'];
        $this->feed_item->body = $form->values['body'];
        $this->feed_item->author = $form->values['author'];
        $this->feed_item->author_link = $form->values['author_link'];
        $this->feed_item->hidden = $form->values['hidden'];

        // Purify item body if body element is active
        if (!empty($this->feed_item->body)) {
            $this->feed_item->body = $this->getPlugin()->getHTMLPurifier($this->feed)->purify($this->feed_item->body);
        }

        if (!$this->feed_item->commit()) return false;

        $this->feed->updateLastPublished();

        return true;
    }
}
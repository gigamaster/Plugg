<?php
class Plugg_Aggregator_Controller_Main_Feed_Item_Delete extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$this->getUser()->hasPermission('aggregator item delete any')) {
            if (!$this->feed_item->isOwnedBy($this->getUser())
                || !$this->getUser()->hasPermission('aggregator item delete own')
            ) {
                return false;
            }
        }

        $this->_cancelUrl = $this->getPlugin()->getUrl($this->feed->id . '/' . $this->feed_item->id);
        $this->_successUrl = $this->getPlugin()->getUrl($this->feed->id);

        $form['#header'][] = sprintf(
            '<div class="plugg-warning">%s</div>',
            $this->_('Are you sure you want to delete this feed item?')
        );
        $form['title'] = array(
            '#type' => 'item',
            '#title' => $this->_('Title'),
            '#markup' => h($this->feed_item->title),
        );

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->feed_item->markRemoved();

        if (!$this->getPluginModel()->commit()) return false;

        $this->feed->updateLastPublished();

        return true;
    }
}
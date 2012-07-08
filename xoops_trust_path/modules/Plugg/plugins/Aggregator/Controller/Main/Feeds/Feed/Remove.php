<?php
class Plugg_Aggregator_Controller_Main_Feeds_Feed_Remove extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$this->getUser()->hasPermission('aggregator feed delete any')) {
            if (!$this->feed->isOwnedBy($this->getUser())
                || !$this->getUser()->hasPermission('aggregator feed delete own')
            ) {
                return false;
            }
        }

        $this->_cancelUrl = $this->_successUrl = $this->getPlugin()->getUrl('feeds');
        $this->_submitButtonLabel = $this->_('Remove feed');

        $form['#header'][] = sprintf(
            '<div class="plugg-warning">%s</div>',
            $this->_('Are you sure you want to delete this feed and all its articles?')
        );
        $form['title'] = array(
            '#type' => 'item',
            '#title' => $this->_('Title'),
            '#markup' => h($this->feed->title),
        );

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->feed->markRemoved();

        return $this->getPluginModel()->commit();
    }
}
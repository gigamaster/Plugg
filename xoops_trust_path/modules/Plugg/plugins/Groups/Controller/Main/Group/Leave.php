<?php
class Plugg_Groups_Controller_Main_Group_Leave extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if ($this->membership->isAdmin()) {
            $response->setError(
                $this->_('You cannot leave this group while you are an administrator.'),
                array('path' => $this->group->id)
            );

            return false;
        }

        $this->_cancelUrl = array();
        $form = array(
            '#header' => array(sprintf('<div class="plugg-warning">%s</div>', $this->_('Are you sure you want to leave this group?'))),
            'group' => array(
                '#type' => 'item',
                '#markup' => $this->Groups_GroupThumbnail($this->group),
                '#title' => $this->_('Group')
            )
        );
        $this->_submitButtonLabel = $this->_('Yes, leave this group');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($this->membership->markRemoved()->commit()) {
            $response->setSuccess(
                $this->_('You have left the group.'),
                $this->group->getUrl()
            );

            return true;
        }

        return false;
    }
}
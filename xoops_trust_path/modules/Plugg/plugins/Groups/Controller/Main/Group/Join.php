<?php
class Plugg_Groups_Controller_Main_Group_Join extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$this->getUser()->isAuthenticated()) {
            // Redirect to login screen if inviation is not required to join this group
            if (!$this->group->isInvitationRequired()) $response->setLoginRequiredError();

            return false;
        }
        
        $this->_cancelUrl = array();
        $form = array(
            'group' => array(
                '#type' => 'item',
                '#markup' => $this->Groups_GroupThumbnail($this->group),
                '#title' => $this->_('Group')
            )
        );
        if ($this->group->isApprovalRequired()) {
            $this->_submitButtonLabel = $this->_('Request membership');
        } else {
            $this->_submitButtonLabel = $this->_('Join this group');
        }

        $this->membership = $this->group->createMember();
        $this->membership->markNew();

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->membership->assignUser($this->getUser());

        // Set the membership as pending if the group requires approval
        if ($this->group->isApprovalRequired()) {
            $this->membership->status = Plugg_Groups_Plugin::MEMBER_STATUS_PENDING;
        } else {
            $this->membership->status = Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE;
        }

        if ($this->membership->commit()) {
            if ($this->membership->isActive()) {
                $response->setSuccess(
                    $this->_('You have joined the group successfully.'),
                    $this->group->getUrl()
                );
            } else {
                $response->setSuccess(
                    $this->_('Thank you for your new group membership request. You will be granted access to the group once the request is approved by the group administrator.')
                );
            }

            return true;
        }

        return false;
    }
}
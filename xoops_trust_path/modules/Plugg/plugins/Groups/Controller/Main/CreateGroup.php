<?php
class Plugg_Groups_Controller_Main_CreateGroup extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_submitButtonLabel = $this->_('Create group');
        $this->_cancelUrl = array();
        
        return $this->getPluginModel()->createForm('Group');
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Create group
        $group = $this->getPluginModel()->create('Group');
        $group->name = $form->values['name'];
        $group->display_name = $form->values['display_name'];
        $group->description = $form->values['description']['text'];
        $group->description_html = $form->values['description']['filtered_text'];
        $group->description_filter_id = $form->values['description']['filter_id'];
        $group->type = $form->values['type'];
        $group->assignUser($this->getUser());
        $group->markNew();
        
        // Avatar uploaded?
        if (!empty($form->values['avatar'])) {
            $group->avatar = $form->values['avatar']['file_name'];
            $group->avatar_icon = $form->values['avatar']['thumbnail'][0];
            $group->avatar_thumbnail = $form->values['avatar']['thumbnail'][1];
            $group->avatar_medium = $form->values['avatar']['thumbnail'][2];
        }

        // Set the group as approved if the user has approve permission
        if ($this->getUser()->hasPermission('groups approve')) {
            $group->status = Plugg_Groups_Plugin::GROUP_STATUS_APPROVED;
        } else {
            $group->status = Plugg_Groups_Plugin::GROUP_STATUS_PENDING;
        }

        // Add the group creator as group admin
        $member = $group->createMember();
        $member->role = Plugg_Groups_Plugin::MEMBER_ROLE_ADMINISTRATOR;
        $member->status = Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE;
        $member->assignUser($this->getUser());
        $member->markNew();

        if ($this->getPluginModel()->commit()) {
            $this->DispatchEvent('GroupsGroupSubmitSuccess', array($group, /*$isEdit*/ false));

            if ($group->isApproved()) {
                $response->setSuccess(
                    $this->_('A new group created successfully.'),
                    $group->getUrl()
                );
            } else {
                $response->setSuccess(
                    $this->_('Thank you for your new group request. Your group will be created once the request is approved by the administrator.')
                );
            }

            return true;
        }

        return false;
    }
}
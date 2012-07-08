<?php
class Plugg_Groups_Controller_Admin_AddGroup extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
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
        $group->status = Plugg_Groups_Plugin::GROUP_STATUS_APPROVED;
        $group->assignUser($this->getUser());
        $group->markNew();
        
        // Avatar uploaded?
        if (!empty($form->values['avatar'])) {
            $group->avatar = $form->values['avatar']['file_name'];
            $group->avatar_icon = $form->values['avatar']['thumbnail'][0];
            $group->avatar_thumbnail = $form->values['avatar']['thumbnail'][1];
            $group->avatar_medium = $form->values['avatar']['thumbnail'][2];
        }

        // Add the group creator as group admin
        $member = $group->createMember();
        $member->role = Plugg_Groups_Plugin::MEMBER_ROLE_ADMINISTRATOR;
        $member->status = Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE;
        $member->assignUser($this->getUser());
        $member->markNew();

        if (!$this->getPluginModel()->commit()) return false;

        $this->_successUrl = $this->getUrl('/groups/' . $group->id);
        $this->DispatchEvent('GroupsGroupSubmitSuccess', array($group, /*$isEdit*/ false));

        return true;
    }
}
<?php
class Plugg_Groups_Controller_Admin_Group_Edit extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = $this->getPluginModel()->createForm($this->group);

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Update group
        $this->group->name = $form->values['name'];
        $this->group->display_name = $form->values['display_name'];
        $this->group->description = $form->values['description']['text'];
        $this->group->description_html = $form->values['description']['filtered_text'];
        $this->group->description_filter_id = $form->values['description']['filter_id'];
        $this->group->type = $form->values['type'];

        // Avatar uploaded?
        if (!empty($form->values['avatar'])) {
            // Remove previous avatar files if any
            $this->group->unlinkAvatars();
            
            $this->group->avatar = $form->values['avatar']['file_name'];
            $this->group->avatar_icon = $form->values['avatar']['thumbnail'][0];
            $this->group->avatar_thumbnail = $form->values['avatar']['thumbnail'][1];
            $this->group->avatar_medium = $form->values['avatar']['thumbnail'][2];
        }

        if (!$this->getPluginModel()->commit()) return false;

        $this->DispatchEvent('GroupsGroupSubmitSuccess', array($this->group, /*$isEdit*/ true));

        return true;
    }
}
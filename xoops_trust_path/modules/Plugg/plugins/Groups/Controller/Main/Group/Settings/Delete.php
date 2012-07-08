<?php
class Plugg_Groups_Controller_Main_Group_Settings_Delete extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_submitButtonLabel = $this->_('Delete group');
        $this->_cancelUrl = array();

        $form['#header'][] = sprintf(
            '<div class="plugg-warning">%s</div>',
            $this->_('Are you sure you want to delete this group?')
        );
        $form['group'] = array(
            '#type' => 'item',
            '#title' => $this->_('Group'),
            '#markup' => $this->Groups_GroupThumbnail($this->group),
        );

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->group->markRemoved();
        if ($this->group->commit()) {
            $this->DispatchEvent('GroupsGroupDeleteSuccess', array($this->group));
            $response->setSuccess(
                $this->_('Group deleted successfully.'),
                $this->getUrl('/groups')
            );
            
            // Remove avatar files if any
            $this->group->unlinkAvatars();

            return true;
        }

        return false;
    }
}
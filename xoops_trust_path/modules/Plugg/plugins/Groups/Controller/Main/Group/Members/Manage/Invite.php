<?php
class Plugg_Groups_Controller_Main_Group_Members_Manage_Invite extends Plugg_Form_Controller
{
    private $_identity;

    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'user_name' => array(
                '#type' => 'textfield',
                '#title' => $this->_('User name'),
                '#size' => 30,
                '#maxlength' => 255,
                '#required' => true,
                '#element_validate' => array(
                    array($this, 'validateUser'),
                    array(array($this, 'validateUserIsNotMember'), array($request)),
                ),
            ),
        );

        $this->_submitButtonLabel = $this->_('Invite user');
        $this->_cancelUrl = array();

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $member = $this->getPluginModel()->create('Member');
        $member->assignGroup($this->group);
        $member->assignUser($this->_identity);
        $member->status = Plugg_Groups_Plugin::MEMBER_STATUS_INVITED;
        $member->markNew();

        if (!$member->commit()) return false;

        $response->setSuccess(
            sprintf($this->_('%s invited to the group successfully.'), $this->_identity->display_name),
            $this->group->getUrl('members/manage')
        );
        $this->getPlugin()->sendGroupInvitationEmail($this->group, $this->_identity);

        return true;
    }

    public function validateUser($form, $value, $name)
    {
        $this->_identity = $this->getPlugin('User')->getIdentityFetcher()
            ->fetchUserIdentityByUsername($value);

        if ($this->_identity->isAnonymous()) {
            $form->setError($this->_('The user does not exist.'), $name);

            return false;
        }

        return true;
    }

    public function validateUserIsNotMember($form, $value, $name, Sabai_Request $request)
    {
        if ($this->getPluginModel()->Member->criteria()
                ->userId_is($this->_identity->id)
                ->groupId_is($this->group->id)
                ->count()
        ) {
            $form->setError($this->_('The user is already a member of the group.'), $name);

            return false;
        }

        return true;
    }
}
<?php
class Plugg_User_Controller_Admin_Roles_Role_AddMember extends Plugg_Form_Controller
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

        $this->_cancelUrl = $this->getPlugin()->getUrl('roles/' . $this->role->id);

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $member = $this->getPluginModel()->create('Member');
        $member->assignRole($this->role);
        $member->assignUser($this->_identity);
        $member->markNew();

        if (!$member->commit()) return false;

        $response->setSuccess(
            $this->_('The role has been assigned to the user successfully.'),
            $this->getPlugin()->getUrl('roles/' . $this->role->id)
        );

        return true;
    }

    public function validateUser($form, $value, $name)
    {
        $this->_identity = $this->getPlugin()->getIdentityFetcher()
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
                ->roleId_is($this->role->id)
                ->count()
        ) {
            $form->setError($this->_('The role is already assigned to the user.'), $name);

            return false;
        }

        return true;
    }
}
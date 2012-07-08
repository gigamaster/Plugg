<?php
class Plugg_User_Controller_Main_Identity_Settings_Password extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_cancelUrl = array();
        $form = array(
            'passwords' => array(
                '#type' => 'fieldset',
                'password' => array(
                    '#type' => 'password',
                    '#title' => $this->_('New password'),
                    '#description' => $this->_('Enter your new password'),
                    '#size' => 50,
                    '#required' => true,
                ),
                'password_confirm' => array(
                    '#type' => 'password',
                    '#title' => $this->_('Confirm password'),
                    '#description' => $this->_('Enter again for confirmation'),
                    '#size' => 50,
                    '#required' => true,
                )
            )
        );
        $form = $this->getPlugin()->getManagerPlugin()->userEditPasswordGetForm($this->identity, $form);
        $form['#action'] = $this->getUrl('/user/' . $this->identity->id . '/settings/password');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($this->getPlugin()->getManagerPlugin()->userEditPasswordSubmitForm($this->identity, $form)) {
            $this->DispatchEvent('UserIdentityEditPasswordSuccess', array($this->identity));

            return true;
        }

        return false;
    }

    public function validateForm(Plugg_Form_Form $form, Sabai_Request $request)
    {
        if (!empty($form->values['password']) &&
            !empty($form->values['password_confirm']) &&
            $form->values['password'] != $form->values['password_confirm']
        ) {
            $form->setError($this->_('The passwords do not match'), 'passwords');
        }
    }
}
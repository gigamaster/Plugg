<?php
class Plugg_User_Controller_Admin_Settings_Email extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $tags = array();
        $form = array(
            'User' => array(
                '#tree' => true,
                '#type' => 'fieldset',
                'register_confirm_email' => array(
                    '#title' => $this->_('Registration confirmation email'),
                    '#collapsible' => true,
                    '#description' => sprintf($this->_('Customize email messages sent to users upon user registration. Available tags are: %s'), '{SITE_NAME} {SITE_URL} {USER_NAME} {USER_EMAIL} {CONFIRM_LINK} {IP}'),
                    'subject' => array(
                        '#title' => $this->_('Subject'),
                        '#type' => 'textfield',
                        '#required' => true,
                        '#default_value' => $this->getPlugin()->getConfig('register_confirm_email', 'subject'),
                    ),
                    'body' => array(
                        '#title' => $this->_('Body'),
                        '#type' => 'textarea',
                        '#required' => true,
                        '#rows' => 11,
                        '#default_value' => $this->getPlugin()->getConfig('register_confirm_email', 'body'),
                    )
                ),

                'register_complete_email' => array(
                    '#collapsible' => true,
                    '#title' => $this->_('Account activation email'),
                    '#description' => sprintf($this->_('Customize email messages sent to users upon account activation. Available tags are: %s'), '{SITE_NAME} {SITE_URL} {USER_NAME} {USER_EMAIL} {USER_LINK} {IP}'),
                    'enable' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Notify user when account is activated.'),
                        '#default_value' => $this->getPlugin()->getConfig('register_complete_email', 'enable'),
                    ),
                    'subject' => array(
                        '#title' => $this->_('Subject'),
                        '#type' => 'textfield',
                        '#required' => true,
                        '#default_value' => $this->getPlugin()->getConfig('register_complete_email', 'subject'),
                    ),
                    'body' => array(
                        '#title' => $this->_('Body'),
                        '#type' => 'textarea',
                        '#required' => true,
                        '#rows' => 11,
                        '#default_value' => $this->getPlugin()->getConfig('register_complete_email', 'body'),
                    ),
                ),

                'edit_email_confirm_email' => array(
                    '#collapsible' => true,
                    '#title' => $this->_('Email modification confirmation email'),
                    '#description' => sprintf($this->_('Customize email messages sent to users upon user email modification. Available tags are: %s'), '{SITE_NAME} {SITE_URL} {USER_NAME} {USER_EMAIL} {CONFIRM_LINK} {IP}'),
                    'subject' => array(
                        '#title' => $this->_('Subject'),
                        '#type' => 'textfield',
                        '#required' => true,
                        '#default_value' => $this->getPlugin()->getConfig('edit_email_confirm_email', 'subject'),
                    ),
                    'body' => array(
                        '#title' => $this->_('Body'),
                        '#type' => 'textarea',
                        '#required' => true,
                        '#rows' => 11,
                        '#default_value' => $this->getPlugin()->getConfig('edit_email_confirm_email', 'body'),
                    ),
                ),

                'new_password_confirm_email' => array(
                    '#collapsible' => true,
                    '#title' => $this->_('Password request confirmation email'),
                    '#description' => sprintf($this->_('Customize email messages sent to users upon new password request. Available tags are: %s'), '{SITE_NAME} {SITE_URL} {USER_NAME} {USER_EMAIL} {CONFIRM_LINK} {IP}'),
                    'subject' => array(
                        '#title' => $this->_('Subject'),
                        '#type' => 'textfield',
                        '#required' => true,
                        '#default_value' => $this->getPlugin()->getConfig('new_password_confirm_email', 'subject'),
                    ),
                    'body' => array(
                        '#title' => $this->_('Body'),
                        '#type' => 'textarea',
                        '#required' => true,
                        '#rows' => 13,
                        '#default_value' => (null !== $value = $this->getPlugin()->getConfig('new_password_confirm_email', 'body')) ? $value : implode(PHP_EOL . PHP_EOL, array(
                            $this->_('Hello {USER_NAME},'),
                            $this->_('A web user from {IP} has just requested a new password for your user account at {SITE_NAME}.'),
                            $this->_('Please click on the link below to confirm the request and receive a new password:'),
                            '{CONFIRM_LINK}',
                            $this->_('If you did not ask for this, you can just ignore this email.'),
                            '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}'
                        )),
                    ),
                ),

                'new_password_email' => array(
                    '#collapsible' => true,
                    '#title' => $this->_('New password email'),
                    '#description' => sprintf($this->_('Customize email messages sent to users containing new password. Available tags are: %s'), '{SITE_NAME} {SITE_URL} {USER_NAME} {USER_PASSWORD} {LOGIN_LINK} {IP}'),
                    'subject' => array(
                        '#title' => $this->_('Subject'),
                        '#type' => 'textfield',
                        '#required' => true,
                        '#default_value' => (null !== $value = $this->getPlugin()->getConfig('new_password_email', 'subject')) ? $value : $this->_('New user password for {USER_NAME}@{SITE_NAME}'),
                    ),
                    'body' => array(
                        '#title' => $this->_('Body'),
                        '#type' => 'textarea',
                        '#required' => true,
                        '#rows' => 16,
                        '#default_value' => $this->getPlugin()->getConfig('new_password_email', 'body'),
                    ),
                )
/*
                'register_confirm_admin_email' => array(
                    '#collapsible' => true,
                    '#title' => $this->_('Registration confirmation email'),
                    '#description' => sprintf($this->_('Customize email messages sent to users upon new password request. Available tags are: %s'), implode(' ', $tags)),
                    'subject' => array(
                        'label' => $this->_('Subject'),
                        'type' => 'textfield',
                        'rules' => array('required' => true),
                        'value' => $this->getPlugin()->getConfig(
                            'register_confirm_admin_email_subject',
                            $this->_('User activation required for {USER_NAME}@{SITE_NAME}')
                        ),
                    ),
                    'body' => array(
                        'label' => $this->_('Body'),
                        'type' => 'textarea',
                        'rules' => array('required' => true),
                        'settings' => array('rows' => 11),
                        'value' => $this->getPlugin()->getConfig('register_admin_confirm_email_body', implode(PHP_EOL . PHP_EOL, array(
                            $this->_('Hello admin,'),
                            $this->_('A new user {USER_NAME} ({USER_EMAIL}) has just registered an account at {SITE_NAME}.'),
                            $this->_('Click on the link below to activate the user account:'),
                            '{CONFIRM_LINK}',
                            '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}',
                        ))),
                    ),

            'user[register_complete_admin_email_subject]' => array(
                'label' => $this->_('Subject'),
                'type' => 'textfield',
                'rules' => array('required' => true),
                'value' => $this->getPlugin()->getConfig(
                    'register_complete_admin_email_subject',
                    $this->_('User registration completed for {USER_NAME}@{SITE_NAME}')
                ),
                'group' => 'user_register_complete_admin_email',
            ),
            'user[register_complete_admin_email_body]' => array(
                'label' => $this->_('Body'),
                'type' => 'textarea',
                'rules' => array('required' => true),
                'settings' => array('rows' => 11),
                'value' => $this->getPlugin()->getConfig('register_complete_admin_email_body', implode(PHP_EOL . PHP_EOL, array(
                    $this->_('Hello admin,'),
                    $this->_('A new user {USER_NAME} ({USER_EMAIL}) has completed user registration at {SITE_NAME}.'),
                    $this->_('Click the link below to view the user profile:'),
                    '{USER_LINK}',
                    '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}',
                ))),
                'group' => 'user_register_complete_admin_email',*/
            ),
        );
        
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values['User'])) {
            if (!$this->getPlugin()->saveConfig($form->values['User'])) return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}
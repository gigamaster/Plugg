<?php
class Plugg_Form_Controller_Admin_Form_EditMailSettings extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $settings = $this->form->getEmailSettings();
        $tags = implode(' ', array_keys($this->getPlugin()->getEmailTags($this->form)));
        $form = array(
            'Form' => array(
                '#tree' => true,
                'confirmation' => array(
                    '#title' => $this->_('Confirmation email'),
                    '#description' => sprintf($this->_('Customize the content of confirmation email sent to users submitted the form. Available tags are: %s'), $tags),
                    'enable' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Enable confirmation email.'),
                        '#default_value' => !empty($settings['confirmation']['enable']),
                    ),
                    'to' => array(
                        '#title' => $this->_('Email to address'),
                        '#description' => $this->_('A confrirmation message will be sent to one of the mail addresses selected. If no match is found, the message will not be sent.'),
                        'user' => array(
                            '#type' => 'checkbox',
                            '#title' => $this->_('If an authenticated user, send to the registered email address of the user.'),
                            '#default_value' => isset($settings['confirmation']['to']['user']) ? $settings['confirmation']['to']['user'] : true,
                        ),
                    ),
                    'subject' => array(
                        '#title' => $this->_('Email subject'),
                        '#type' => 'textfield',
                        '#required' => true,
                        '#default_value' => isset($settings['confirmation']['subject']) ? $settings['confirmation']['subject'] : $this->_('Form submission from: {FORM_TITLE}'),
                    ),
                    'body' => array(
                        '#title' => $this->_('Email body'),
                        '#type' => 'textarea',
                        '#required' => true,
                        '#rows' => 11,
                        '#default_value' => isset($settings['confirmation']['body']) ? $settings['confirmation']['body'] : implode(PHP_EOL . PHP_EOL, array(
                            $this->_('Hello {USER_NAME},'),
                            $this->_('You are now a member of the group {GROUP_NAME} at {SITE_NAME}.'),
                            $this->_('Visit the group page at the following URL:'),
                            '{GROUP_MAIN_URL}',
                            '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}',
                        )),
                    ),
                ),
                'notification' => array(
                    '#title' => $this->_('Notification email'),
                    '#description' => sprintf($this->_('Customize the content of notification email sent to the administrators upon form submission. Available tags are: %s'), $tags),
                    'enable' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Enable notification email.'),
                        '#default_value' => !empty($settings['notification']['enable']),
                    ),
                    'to' => array(
                        '#type' => 'checkboxes',
                        '#title' => $this->_('Email to address'),
                        '#description' => $this->_('Notifications will be sent to the selected mail addresses.'),
                        '#options' => array($this->SiteEmail() => $this->SiteEmail()),
                        '#other' => array('enable' => true, 'type' => 'email', 'attributes' => array('size' => 30, 'style' => 'width:21em;')),
                        '#default_value' => @$settings['notification']['to'],
                    ),
                    'subject' => array(
                        '#title' => $this->_('Email subject'),
                        '#type' => 'textfield',
                        '#default_value' => isset($settings['notification']['subject']) ? $settings['notification']['subject'] : $this->_('Form submission from: {FORM_TITLE}'),
                    ),
                    'body' => array(
                        '#title' => $this->_('Email body'),
                        '#type' => 'textarea',
                        '#rows' => 11,
                        '#default_value' => isset($settings['notification']['body']) ? $settings['notification']['body'] : implode(PHP_EOL . PHP_EOL, array(
                            $this->_('Hello {USER_NAME},'),
                            $this->_('You have been invited to the group {GROUP_NAME} at {SITE_NAME}.'),
                            $this->_('To accept invitation and join the group, click on the link below:'),
                            '{GROUP_JOIN_URL}',
                            '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}',
                        ))
                    ),
                ),
            )
        );
        
        $this->_submitButtonLabel = $this->_('Save configuration');
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['Form'])) return;
        
        $this->form->setEmailSettings($form->values['Form']);
        
        return $this->form->commit();  
    }
}
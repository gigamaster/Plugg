<?php
class Plugg_Groups_Controller_Admin_Settings extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        // Get available email tags
        $tags = implode(' ', array_keys($this->getPlugin()->getEmailTags(
            $this->getPluginModel()->create('Group'), $this->getUser() // pass in dummy group/user objects
        )));

        $form = array(
            'Groups' => array(
                '#tree' => true,
                'joinRequestApprovedEmail' => array(
                    '#collapsible' => true,
                    '#title' => $this->_('Membership activation email'),
                    '#description' => sprintf($this->_('Customize email messages sent to users upon membership activation. Available tags are: %s'), $tags),
                    'enable' => array(
                        '#type' => 'checkbox',
                        '#title' => $this->_('Notify user when group membership is activated.'),
                        '#default_value' => $this->getPlugin()->getConfig('joinRequestApprovedEmail', 'enable'),
                    ),
                    'subject' => array(
                        '#title' => $this->_('Subject'),
                        '#type' => 'textfield',
                        '#required' => true,
                        '#default_value' => $this->getPlugin()->getConfig('joinRequestApprovedEmail', 'subject'),
                    ),
                    'body' => array(
                        '#title' => $this->_('Body'),
                        '#type' => 'textarea',
                        '#required' => true,
                        '#rows' => 11,
                        '#default_value' => $this->getPlugin()->getConfig('joinRequestApprovedEmail', 'body'),
                    ),
                ),
                'invitationEmail' => array(
                    '#collapsible' => true,
                    '#title' => $this->_('Group invitation email'),
                    '#description' => sprintf($this->_('Customize group invitation messages sent to users. Available tags are: %s'), $tags),
                    'subject' => array(
                        '#title' => $this->_('Subject'),
                        '#type' => 'textfield',
                        '#required' => true,
                        '#default_value' => $this->getPlugin()->getConfig('invitationEmail', 'subject'),
                    ),
                    'body' => array(
                        '#title' => $this->_('Body'),
                        '#type' => 'textarea',
                        '#required' => true,
                        '#rows' => 11,
                        '#default_value' => $this->getPlugin()->getConfig('invitationEmail', 'body'),
                    ),
                ),
            ),
        );
        
        $this->_cancelUrl = null;
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values['Groups'])) {
            if (!$this->getPlugin()->saveConfig($form->values['Groups'])) return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}
<?php
class Plugg_Aggregator_Controller_Admin_Settings_Email extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'Aggregator' => array(
                '#tree' => true,
                'approvedNotifyEmail' => array (
                    '#title' => $this->_('Feed approved notification email'),
                    '#description' => sprintf($this->_('Customize email messages sent to users upon feed approval. Available tags are: %s'), '{SITE_NAME} {SITE_URL} {USER_NAME} {FEED_TITLE} {FEED_FEED_URL} {FEED_MAIN_URL} {FEED_USER_URL} {FEED_PING_URL}'),
                    'enable' => array(
                        '#title' => $this->_('Enable feed approved notification email'),
                        '#required' => true,
                        '#type' => 'checkbox',
                        '#default_value' => $this->getPlugin()->getConfig('approvedNotifyEmail', 'enable'),
                    ),
                      'subject' => array(
                        '#title' => $this->_('Subject'),
                        '#required' => true,
                        '#type' => 'textfield',
                        '#default_value' => $this->getPlugin()->getConfig('approvedNotifyEmail' , 'subject'),
                    ),
                    'body' => array(
                        '#title' => $this->_('Body'),
                        '#type' => 'textarea',
                        '#required' => true,
                        '#rows' => 20,
                        '#default_value' => $this->getPlugin()->getConfig('approvedNotifyEmail', 'body'),
                    ),
                ),
                'addedNotifyEmail' => array(
                    '#title' => $this->_('Feed added notification email'),
                    '#description' => sprintf($this->_('Customize email messages sent to users upon feed registration by administrator. Available tags are: %s'), '{SITE_NAME} {SITE_URL} {USER_NAME} {FEED_TITLE} {FEED_FEED_URL} {FEED_MAIN_URL} {FEED_USER_URL} {FEED_PING_URL}'),
                    'enable' => array(
                        '#title' => $this->_('Enable feed added notification email'),
                        '#required' => true,
                        '#type' => 'checkbox',
                        '#default_value' => $this->getPlugin()->getConfig('addededNotifyEmail', 'enable'),
                    ),
                    'subject' => array(
                        '#title' => $this->_('Subject'),
                        '#required' => true,
                        '#type' => 'textfield',
                        '#default_value' => $this->getPlugin()->getConfig('addedNotifyEmail', 'subject'),
                    ),
                    'body' => array(
                        '#title' => $this->_('Body'),
                        '#type' => 'textarea',
                        '#required' => true,
                        '#rows' => 20,
                        '#default_value' => $this->getPlugin()->getConfig('addedNotifyEmail', 'body'),
                    ),
                ),
            ),
        );
        
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values['Aggregator'])) {
            if (!$this->getPlugin()->saveConfig($form->values['Aggregator'])) {
                return false;
            }
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}
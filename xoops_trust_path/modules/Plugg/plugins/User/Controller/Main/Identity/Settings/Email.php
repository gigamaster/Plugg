<?php
class Plugg_User_Controller_Main_Identity_Settings_Email extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_cancelUrl = array();

        $form = array(
            'emails' => array(
                '#type' => 'fieldset',
                '#description' => $this->_('Please enter a valid email address for yourself. We will send you an email shortly that you must confirm to complete the process.'),
                'email' => array(
                    '#type' => 'email',
                    '#title' => $this->_('New email address'),
                    '#description' => $this->_('Enter your new email address'),
                    '#default_value' => $this->identity->email,
                    '#size' => 50,
                    '#maxlength' => 255,
                    '#required' => true,
                ),
                'email_confirm' => array(
                    '#type' => 'email',
                    '#title' => $this->_('Confirm email address'),
                    '#description' => $this->_('Enter again for confirmation'),
                    '#size' => 50,
                    '#default_value' => $this->identity->email,
                    '#maxlength' => 255,
                    '#required' => true,
                )
            ),
        );
        $form = $this->getPlugin()->getManagerPlugin()->userEditEmailGetForm($this->identity, $form);
        $form['#action'] = $this->getUrl('/user/' . $this->identity->id . '/settings/email');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $queue = $this->getPluginModel()->create('Queue');
        if ($this->getPlugin()->getManagerPlugin()->userEditEmailQueueForm($queue, $this->identity, $form)) {
             $queue->key = md5(uniqid(mt_rand(), true));
             $queue->type = Plugg_User_Plugin::QUEUE_TYPE_EDITEMAIL;
             $queue->identity_id = $this->identity->id;
             $queue->markNew();
             if ($queue->commit()) {
                // Process the queue right now if email address has not been modified
                if ($this->identity->email == $queue->notify_email) {
                    $request->set('key', $queue->key);
                    $this->forward('/user/confirm/' . $queue->id, $request, $response);
                    return;
                }

                // Send confirmation email
                $this->getPlugin()->sendEditEmailConfirmEmail($queue);

                $response->setContent($this->_('Email modification request has been submitted successfully. Please check your email for further instruction.'));

                return true;
            }
        }

        return false;
    }

    public function validateForm(Plugg_Form_Form $form, Sabai_Request $request)
    {
        if (!empty($form->values['email'])
            && !empty($form->values['email_confirm'])
            && $form->values['email'] != $form->values['email_confirm']
        ) {
            $form->setError($this->_('The email addresses do not match'), 'emails');
        }
    }
}
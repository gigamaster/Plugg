<?php
class Plugg_User_Controller_Main_RequestPassword extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        // Check if already registered and logged in
        if ($this->getUser()->isAuthenticated()) {
            $response->setError(null, array('base' => '/user'));
            return false;
        }

        // Check if user account plugin is valid
        if ((!$manager_name = $this->getPlugin()->getConfig('userManagerPlugin')) ||
            (!$manager = $this->getPlugin($manager_name))
        ) {
            return false;
        }

        // Is it an API type plugin?
        if ($manager instanceof Plugg_User_Manager_API) {
            $manager->userRequestPassword($request, $response);
            return;
        }

        $form = array(
            '#header' => array($this->_('If you have forgotten your username or password, you can request to have your username emailed to you and to reset your password. When you fill in your registered email address, you will be sent instructions on how to reset your password.')),
            'email' => array(
                '#type' => 'email',
                '#title' => $this->_('Email address'),
                '#description' => $this->_('Enter your registered email address.'),
                '#size' => 50,
                '#maxlength' => 255,
                '#required' => true,
            ),
        );
        $form = $manager->userRequestPasswordGetForm($form);
        $form['#action'] = $this->createUrl(array('path' => 'request_password'));

        $this->_submitButtonLabel = $this->_('Request password');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $queue = $this->getPluginModel()->create('Queue');
        $manager = $this->getPlugin()->getManagerPlugin();
        if ($manager->userRequestPasswordQueueForm($queue, $form)
            && ($identity_id = $queue->identity_id) // make sure identity id is set by the manager
        ) {
            $identity = $this->getPlugin()->getIdentity($identity_id);
            if (!$identity->isAnonymous()) {
                $queue->key = md5(uniqid(mt_rand(), true));
                $queue->type = Plugg_User_Plugin::QUEUE_TYPE_REQUESTPASSWORD;
                $queue->markNew();
                if ($queue->commit()) {
                    // Send confirmation email
                    $this->getPlugin()->sendRequestPasswordConfirmEmail($queue, $identity);

                    $response->setContent($this->_('Password request has been submitted successfully. Please check your email for further instruction.'));

                    return true;
                }
            }
        }

        return false;
    }
}
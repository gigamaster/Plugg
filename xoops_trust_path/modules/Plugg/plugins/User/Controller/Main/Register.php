<?php
class Plugg_User_Controller_Main_Register extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        // Check if already registered
        if ($this->getUser()->isAuthenticated()) {
            $response->setError(null, array('base' => '/user'));
            return false;
        }

        // Is it an API type plugin?
        $manager = $this->getPlugin()->getManagerPlugin();
        if ($manager instanceof Plugg_User_Manager_API) {
            $manager->userRegister($request, $response);
            return;
        }

        $form = $this->getPlugin()->getRegisterForm();
        $form['#action'] = $this->createUrl(array('path' => 'register'));

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $manager = $this->getPlugin()->getManagerPlugin();
        $extra_fields = $this->getPlugin()->extractExtraFormFieldValues($form->values);
        $model = $this->getPluginModel();
        $queue = $model->create('Queue');
        if ($manager->userRegisterQueueForm($queue, $form)) {
            $queue->setExtraData($extra_fields);
            $queue->key = md5(uniqid(mt_rand(), true));
            $queue->type = Plugg_User_Plugin::QUEUE_TYPE_REGISTER;

            if ('auto' == $activation_type = $this->getPlugin()->getConfig('userActivation')) {
                 // Activate user now
                 if ($identity = $manager->userRegisterSubmit($queue)) {
                    // Save extra data if any
                    if ($extra_data = $queue->getExtraData()) {
                        $this->User_IdentitySaveMeta($identity, array(
                            'user_fields' => $extra_data['value'],
                            'user_fields_visibility' => $extra_data['visibility']
                        ));
                    }
                    $response->setSuccess(
                        $this->_('You have been registered successfully. Please login using the username/password pair submitted during registration.'),
                        array('path' => 'login')
                    );
                    // Dispatch UserRegisterSuccess event
                    $this->DispatchEvent('UserRegisterSuccess', array($identity));

                    return true;
                }
            } else {
                // Save registration data into queue
                $queue->markNew();
                if ($model->commit()) {
                    // Confirm by admin?
                    $confirm_by_admin = 'admin' == $activation_type;

                    // Send confirmation email
                    $this->getPlugin()->sendRegisterConfirmEmail($queue, $confirm_by_admin);

                    if ($confirm_by_admin) {
                        $msg = $this->_('Registration data has been submitted successfully. Your account will be activated after confirmation by the administrator.');
                    } else {
                        $msg = $this->_('Registration data has been submitted successfully. Please check your email for further instruction.');
                    }
                    $response->setContent($msg);
                }
            }
        }
    }
}
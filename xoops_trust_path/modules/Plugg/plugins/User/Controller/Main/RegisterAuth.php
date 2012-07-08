<?php
class Plugg_User_Controller_Main_RegisterAuth extends Sabai_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        // Check if properly coming from authentication
        if (empty($_SESSION['Plugg_User_Main_Login_auth']['timestamp']) ||
            $_SESSION['Plugg_User_Main_Login_auth']['timestamp'] < time() - 300
        ) {
            return false;
        }

        // Check if already registered
        if ($this->getUser()->isAuthenticated()) {
            unset($_SESSION['Plugg_User_Main_Login_auth']);

            return false;
        }

        // Check if user account plugin is valid
        $manager = $this->getPlugin()->getManagerPlugin();
        if ($manager instanceof Plugg_User_Manager_API) {
            unset($_SESSION['Plugg_User_Main_Login_auth']);

            return false;
        }

        // Update auth data timestamp in session
        $_SESSION['Plugg_User_Main_Login_auth']['timestamp'] = time();

        $form = $this->getPlugin()->getRegisterForm();
        $form['#action'] = $this->createUrl(array('path' => 'register_auth'));
        $form['#header'] = array(sprintf(
            $this->_('If you already have a user account, you can <a href="%s">associate the submitted authentication with that account</a>.'),
            $this->createUrl(array('base' => '/user', 'path' => 'associate_auth'))
        ));

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $extra_fields = $this->getPlugin()->extractExtraFormFieldValues($form->values);
        $model = $this->getPluginModel();
        $manager = $this->getPlugin()->getManagerPlugin();
        if ($manager->userRegisterQueueForm($queue, $form)) {
            $queue->setExtraData($extra_fields);
            $queue->key = md5(uniqid(mt_rand(), true));
            $queue->type = Plugg_User_Plugin::QUEUE_TYPE_REGISTERAUTH;
            $queue->setAuthData($_SESSION['Plugg_User_Main_Login_auth']);

            if ('auto' == $activation_type = $this->getPlugin()->getConfig('userActivation')) {
                // Activate user now
                if ($identity = $manager->userRegisterSubmit($queue)) {
                    unset($_SESSION['Plugg_User_Main_Login_auth']);

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

                    // Save associated authentication data
                    $auth_data = $queue->getAuthData();
                    if ($this->getPlugin()->createAuthdata($auth_data, $identity->id)) {
                        $response->addMessage(
                            sprintf(
                                $this->_('Additionally, your external authentication data using %s has been associated with the created account. You can also use that authentication data to login.'),
                                $auth_data['type']
                            ),
                            Sabai_Application_Response::MESSAGE_SUCCESS
                        );
                    } else {
                        $url = $this->createUrl(array(
                            'base' => '/user',
                            'path' => 'login',
                            'params' => array('_auth' => $auth_data['type'])
                        ));
                        $response->addMessage(
                            sprintf(
                                $this->_('An error has occurred while associating your external authentication data with the created account. Please <a href="%s">login again using %s</a> if you need to associate the authentication data.'),
                                $url,
                                $auth_data['type']
                            ),
                            Sabai_Application_Response::MESSAGE_WARNING
                        );
                    }

                    // Dispatch UserRegisterSuccess event
                    $this->DispatchEvent('UserRegisterSuccess', array($identity));

                    return true;
                }
            } else {
                // Save registration data into queue
                $queue->markNew();
                if ($model->commit()) {
                    unset($_SESSION['Plugg_User_Main_Login_auth']);

                    // Confirm by admin?
                    $confirm_by_admin = 'admin' == $this->getPlugin()->getConfig('userActivation');

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
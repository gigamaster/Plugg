<?php
class Plugg_User_Controller_Main_Confirm extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Redirected from the admin page?
        $fromAdmin = $this->getUser()->isSuperUser() && $request->asBool('admin');

        $url = $fromAdmin ? array('script' => 'admin', 'base' => '/user/queues') : array('base' => '/user');

        // Check if user account plugin is valid
        if ((!$manager_name = $this->getPlugin()->getConfig('userManagerPlugin')) ||
            (!$manager = $this->getPlugin($manager_name)) ||
            $manager instanceof Plugg_User_Manager_API
        ) {
            $response->setError($this->_('Invalid request'), $url);

            return;
        }

        // Check if confirmation request
        if ((!$queue = $this->_isValidQueueRequested($request))) {
            $response->setError($this->_('Invalid request'), $url);

            return;
        }

        // Remove queue
        $queue->markRemoved();
        if (!$queue->commit()) {
            $response->setContent($this->_('An error occurred. Please click on the email link again to complete the process.'));

            return;
        }

        switch ($queue->type) {
            case Plugg_User_Plugin::QUEUE_TYPE_REGISTER:
                $this->_processRegisterQueue($request, $response, $queue, $manager, $fromAdmin);
                break;
            case Plugg_User_Plugin::QUEUE_TYPE_REQUESTPASSWORD:
                $this->_processRequestPasswordQueue($request, $response, $queue, $manager, $fromAdmin);
                break;
            case Plugg_User_Plugin::QUEUE_TYPE_EDITEMAIL:
                $this->_processEditEmailQueue($request, $response, $queue, $manager, $fromAdmin);
                break;
            case Plugg_User_Plugin::QUEUE_TYPE_REGISTERAUTH:
                $this->_processRegisterAuthQueue($request, $response, $queue, $manager, $fromAdmin);
                break;
            default:
                $response->setError(
                    $this->_('Invalid request'),
                    $url
                );
        }
    }

    private function _isValidQueueRequested(Sabai_Request $request)
    {
        if (($queue = $this->getPlugin()->getRequestedEntity($request, 'Queue', 'queue_id'))
            && $queue->key == $request->asStr('key')
        ) {
            return $queue;
        }

        return false;
    }

    private function _processRegisterQueue(Sabai_Request $request, Sabai_Application_Response $response, $queue, $manager, $fromAdmin)
    {
        $identity = $manager->userRegisterSubmit($queue);

        if ($identity instanceof Sabai_User_Identity) {
            // Save extra data if any
            if ($extra_data = $queue->getExtraData()) {
                $this->User_IdentitySaveMeta($identity, array(
                    'user_fields' => $extra_data['value'],
                    'user_fields_visibility' => $extra_data['visibility']
                ));
            }
            $response->setSuccess(
                $this->_('You have been registered successfully. Please login using the username/password pair submitted during registration.'),
                $fromAdmin ? array('script' => 'admin', 'base' => '/user/queues') : array('path' => 'login')
            );
            // Dispatch UserRegisterSuccess event
            $this->DispatchEvent('UserRegisterSuccess', array($identity));
        } else {
            if (is_string($identity)) {
                $response->addMessage($identity, Sabai_Application_Response::MESSAGE_WARNING);
            }
            $response->setError(
                $this->_('An error occurred. Please fill in the registration form again to register.'),
                $fromAdmin ? array('script' => 'admin', 'base' => '/user/queues') : array('base' => '/user/register')
            );
        }
    }

    private function _processRequestPasswordQueue(Sabai_Request $request, Sabai_Application_Response $response, $queue, $manager, $fromAdmin)
    {
        $identity = $this->getPlugin()->getIdentity($queue->identity_id);
        if (!$identity->isAnonymous() && ($password = $manager->userRequestPasswordSubmit($queue))) {
            $this->_sendNewPasswordEmail($request, $identity, $password, $manager);
            $response->setSuccess(
                $this->_('Your new password has been sent to your registered email address.'),
                $fromAdmin ? array('script' => 'admin', 'base' => '/user/queues') : array('path' => 'login')
            );
            // Dispatch UserRequestPasswordSuccess event
            $this->DispatchEvent('UserRequestPasswordSuccess', array($identity));
        } else {
            $response->setError(
                $this->_('An error occurred. Please submit the password request form again.'),
                $fromAdmin ? array('script' => 'admin', 'base' => '/user/queues') : array('base' => '/user/request_password')
            );
        }
    }

    private function _processEditEmailQueue(Sabai_Request $request, Sabai_Application_Response $response, $queue, $manager, $fromAdmin)
    {
        $identity = $this->getPlugin()->getIdentity($queue->identity_id);
        if (!$identity->isAnonymous() && $manager->userEditEmailSubmit($queue, $identity)) {
            $response->setSuccess(
                $this->_('Your email address has been updated successfully.'),
                $fromAdmin ? array('script' => 'admin', 'base' => '/user/queues') : array('base' => '/user/settings/email')
            );
            // Dispatch UserEditEmailSuccess event
            $this->DispatchEvent('UserEditEmailSuccess', array($identity));
        } else {
            $response->setError(
                $this->_('An error occurred. Please submit the form again to change your email address.'),
                $fromAdmin ? array('script' => 'admin', 'base' => '/user/queues') : array('base' => '/user/settings/email')
            );
        }
    }

    private function _processRegisterAuthQueue(Sabai_Request $request, Sabai_Application_Response $response, $queue, $manager, $fromAdmin)
    {
        $identity = $manager->userRegisterSubmit($queue);

        if ($identity instanceof Sabai_User_Identity) {
            // Save extra data if any
            if ($extra_data = $queue->getExtraData()) {
                $this->User_IdentitySaveMeta($identity, array(
                    'user_fields' => $extra_data['value'],
                    'user_fields_visibility' => $extra_data['visibility']
                ));
            }
            $response->setSuccess(
                $this->_('You have been registered successfully. Please login using the username/password pair submitted during registration.'),
                $fromAdmin ? array('script' => 'admin', 'base' => '/user/queues') : array('path' => 'login')
            );
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
                    'params' => array('_auth' => $auth_data['type']))
                );
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
        } else {
            if (is_string($identity)) {
                $response->addMessage($identity, Sabai_Application_Response::MESSAGE_WARNING);
            }
            $response->setError(
                $this->_('An error occurred. Please fill in the registration form again to register.'),
                $fromAdmin ? array('script' => 'admin', 'base' => '/user/queues') : array('base' => '/user/register')
            );
        }
    }

    private function _sendNewPasswordEmail($request, $identity, $newPassword, $manager)
    {
        $replacements = array(
            '{SITE_NAME}' => $this->SiteName(),
            '{SITE_URL}' => $this->SiteUrl(),
            '{USER_NAME}' => $identity->display_name,
            '{USER_PASSWORD}' => $newPassword,
            '{LOGIN_LINK}' => $this->getUrl('/user/login'),
            '{IP}' => getip()
        );
        $subject = strtr($this->getPlugin()->getConfig('new_password_email', 'subject'), $replacements);
        $body = strtr($this->getPlugin()->getConfig('new_password_email', 'body'), $replacements);

        return $this->getPlugin('Mail')->getSender()
            ->mailSend(
                $identity->email,
                $subject,
                $body
            );
    }
}
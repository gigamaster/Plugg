<?php
class Plugg_User_Controller_Main_AssociateAuth extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        // Check if properly coming from authentication
        if (empty($_SESSION['Plugg_User_Main_Login_auth']['timestamp']) ||
            $_SESSION['Plugg_User_Main_Login_auth']['timestamp'] < time() - 300
        ) {
            return false;
        }

        // Check if already logged in
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

        $form = $this->getPlugin()->getLoginForm(
            $this->getUrl('/user/associate_auth'),
            $request->asInt('_autologin')
        );
        $form['#header'] = array(
            sprintf(
                $this->_('Login using the form below to associate your user account with the submitted authentication or <a href="%s">create a new user account and associate it with the authenticaton</a>.'),
                $this->getUrl('/user/register_auth')
            )
        );
        
        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (($user = $this->getPlugin()->submitLoginForm($form->values))
            && $this->getPlugin()->loginUser($request, $user, @$form->values['_autologin'])
        ) {
            $response->setSuccess($this->_('You have logged in successfully.'), $url);
            
            // Associate authentication in session with the user account
            if ($this->getPlugin()->createAuthdata($_SESSION['Plugg_User_Main_Login_auth'], $user->id)) {
                $response->addMessage(
                    $this->_('Authentication associated with your user account successfully. You can log in as the user account using the authentication from now on.'),
                    Sabai_Application_Response::MESSAGE_SUCCESS
                );
            } else {
                $response->addMessage(
                    $this->_('Failed creating association between your user account and the external authentication used. Try logging in again using the external authentication.'),
                    Sabai_Application_Response::MESSAGE_WARNING
                );
            }
            unset($_SESSION['Plugg_User_Main_Login_auth']);

            return true;
        }

        return false;
    }
}
<?php
class Plugg_User_Controller_Main_Login extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        // Check if already logged in
        if ($this->getUser()->isAuthenticated()) {
            $response->setError(null, array('base' => '/user'));

            return false;
        }

        // Get the URL to redirect
        $return_to = array();
        if ($request->asInt('return')) {
            if (!$return_to = @$request->get('return_to')) {
                $return_to = $this->getPreviousRequestUrl();
            }
        }

        // Is it an API type plugin?
        $manager = $this->getPlugin()->getManagerPlugin();
        if ($manager instanceof Plugg_User_Manager_API) {
            $manager->userLogin($request, $response, $return_to);
            return;
        }

        // Save the return URL into session if not currently submitting the login form
        if (!empty($return_to) && !$request->isPost()) {
            $_SESSION['Plugg_User_Main_Login_return'] = $return_to;
        }
        
        $this->_cancelUrl = null;

        return $this->getPlugin()->getLoginForm();
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (($user = $this->getPlugin()->submitLoginForm($form, $form->values))
            && $this->getPlugin()->loginUser($request, $response, $user, @$form->values['_autologin'])
        ) {
            return true;
        }

        return false;
    }
    
    public function viewForm(Plugg_Form_Form $form, &$formHtml, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $auths = $this->getPluginModel()->Auth->criteria()->active_is(1)->fetch(0, 0, 'order', 'ASC');
        $formHtml = $this->RenderTemplate('user_main_login', array('active_auths' => $auths)) . PHP_EOL . $formHtml;
    }
}
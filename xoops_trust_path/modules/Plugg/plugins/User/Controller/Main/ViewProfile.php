<?php
class Plugg_User_Controller_Main_ViewProfile extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Check user_name parameter
        if (!$user_name = $request->asStr('user_name')) {
            if (!$this->getUser()->isAuthenticated()) {
                $response->setError($this->_('Invalid request'), array('base' => '/'));
            } else {
                $this->forward($this->getUser()->id, $request);
            }

            return;
        }

        // Fetch identity
        $identity = $this->getPlugin()->getIdentityFetcher()->fetchUserIdentityByUsername($user_name);

        // User with the requested user name does not exist
        if ($identity->isAnonymous()) {
            $response->setError($this->_('Invalid request'), array('base' => '/'));

            return;
        }

        // Forward to the user profile page
        $route = sprintf('/user/%d/%s', $identity->id, $this->getNextRoute());
        $this->forward($route, $request, $response);
    }
}
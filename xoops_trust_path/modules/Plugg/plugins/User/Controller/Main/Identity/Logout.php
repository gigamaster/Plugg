<?php
class Plugg_User_Controller_Main_Identity_Logout extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($this->getPlugin()->getManagerPlugin()->userLogoutUser($this->getUser())) {
            $url = null;

            // Set return URL if any
            if ($return_to = $request->asStr('return_to')) {
                // Make sure the URL is a local URL
                if ($this->isLocalUrl($return_to)) $url = $return_to;
            }

            $response->setSuccess($this->_('You have logged out successfully.'), $url);
            $this->DispatchEvent('UserLogoutSuccess', array($this->getUser()));
        } else {
            $response->setError(
                $this->_('An error occurred'),
                array('base' => '/user')
            );
        }
    }
}
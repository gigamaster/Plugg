<?php
class Plugg_User_Controller_Admin_Auths_Auth extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $content = $this->RenderTemplate(
            'user_admin_auths_auth',
            array('auth_plugin_nicename' => $this->getPlugin($this->auth->plugin)->nicename)
        );
        
        $response->setContent($content);
    }
}
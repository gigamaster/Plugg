<?php
class Plugg_MainController_Index extends Sabai_Application_Controller
{
    private static $_done = false;

    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Prevent recursive routing
        if (!self::$_done) {
            self::$_done = true;
            
            $frontpage_config = $this->getPlugin('System')->getConfig('front_page');
            if (!$frontpage_config['path']) $frontpage_config['path'] = 'widgets';
            
            $response->setPageTitle($frontpage_config['show_title'] ? $this->getTitle() : '', true, false);
            
            $this->forward('/' . $frontpage_config['path'], $request, $response);
        }
    }
}
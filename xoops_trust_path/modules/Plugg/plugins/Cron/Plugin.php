<?php
class Plugg_Cron_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Admin
{
    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/system/settings/cron' => array(
                'controller' => 'System_Settings',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path){}

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType){}

    /* End implementation of Plugg_System_Routable_Admin */
}
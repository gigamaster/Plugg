<?php
class Plugg_Content_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Admin
{
    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/content' => array(
                'controller' => 'Index',
                'title' => $this->_('Content management'),
                'type' => Plugg::ROUTE_TAB,
                'title_callback' => true,
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/content':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Content') : $title;
        }
    }

    /* End implementation of Plugg_System_Routable_Admin */
}
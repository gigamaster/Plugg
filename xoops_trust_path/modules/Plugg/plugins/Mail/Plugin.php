<?php
class Plugg_Mail_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Admin
{
    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/system/settings/mail' => array(
                'controller' => 'Settings',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
            ),
            '/system/settings/mail/mailer/:plugin_name' => array(
                'controller' => 'Settings_Mailer',
                'format' => array(':plugin_name' => '\w+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/system/settings/mail/mailer/:plugin_name':
                // Make sure a valid mailer engine plugin is requested
                return ($plugin = $this->_application->getPlugin($request->asStr('plugin_name')))
                    && $plugin instanceof Plugg_Mail_Mailer;
        }
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/system/settings/mail/mailer/:plugin_name':
                return $this->_application->getPlugin($request->asStr('plugin_name'))->mailGetNicename();
        }
    }

    /* End implementation of Plugg_System_Routable_Admin */

    public function getMailerPlugin()
    {
        if ($plugin_name = $this->getConfig('mailerPlugin')) {
            if ($plugin = $this->_application->getPlugin($plugin_name)) {
                return $plugin;
            }
        }
        throw new Plugg_Exception(sprintf('Mailer plugin %s could not be found', $plugin_name));
    }

    public function getSender()
    {
        return $this->getMailerPlugin()->mailGetSender();
    }
}
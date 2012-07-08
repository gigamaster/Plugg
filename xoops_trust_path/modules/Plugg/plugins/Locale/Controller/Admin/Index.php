<?php
class Plugg_Locale_Controller_Admin_Index extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $localizable_plugins = array();
        $installed_plugins = $this->getPluginManager()->getInstalledPlugins();
        foreach (array_keys($installed_plugins) as $plugin_name) {
            if (($plugin = $this->getPlugin($plugin_name)) && $plugin->hasLocale) {
                $localizable_plugins[$plugin_name] = array(
                    'nicename' => $plugin->nicename,
                );
                $plugin_message_count[$plugin_name] = $this->getGettext()->countMessages($plugin_name);
            }
        }
        
        $vars = array(
            'global_message_count' => $this->getGettext()->countMessages(),
            'plugin_message_count' => $plugin_message_count,
            'plugins' => $localizable_plugins,
            'custom_message_count' => $this->getPluginModel()
                ->getGateway('Message')
                ->getPluginMessageCount(),
        );

        $response->setContent($this->RenderTemplate('locale_admin_index', $vars));
    }
}
<?php
class Plugg_System_Controller_Admin_MinifyCss extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Do not save this page to the request log
        $this->setSessionVar('request_url', $this->getPreviousRequestUrl());

        $this->getPlugin()->sendMinifiedCss(array($this, 'getContent'));
    }

    public function getContent()
    {
        $content = array();

        // Load plugin stylesheets
        $plugins = $this->getPluginManager()->getInstalledPlugins();
        foreach (array_keys($plugins) as $plugin_name) {
            if (!$plugin = $this->getPlugin($plugin_name)) continue;

            if ($plugin->hasAdminCSS) {
                $content[] = file_get_contents($plugin->path . '/css/admin.css');
            }
        }

        return str_replace(array('image.php', 'css.php'), array($this->getScript('image'), $this->getScript('css')), implode('', $content));
    }
}
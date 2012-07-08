<?php
class Plugg_MainController extends Plugg_RoutingController
{
    private $_accessCallbacks = array();

    public function __construct()
    {
        parent::__construct('Index', __CLASS__ . '_', dirname(__FILE__) . '/MainController');
        $this->_contentName = 'main';
        $this->_defaultTabAjax = false;
    }

    protected function _getPluginRoutes($pluginPath)
    {
        return $this->getPlugin('System')->getRoutes($pluginPath);
    }

    protected function _processAccessCallback(Sabai_Request $request, Sabai_Application_Response $response, $route)
    {
        // Make sure the callback is called only once for each path
        $path = $route['path'];
        if (!isset($this->_accessCallbacks[$path])) {
            // Change the callback plugin if the plugin of the route is different from that of the current plugin
            $plugin = $route['plugin'] !== $this->getPlugin()->name ? $this->getPlugin($route['plugin']) : $this->getPlugin();
            $this->_accessCallbacks[$path] = $plugin->systemRoutableOnMainAccess($request, $response, $path);
        }

        return $this->_accessCallbacks[$path];
    }

    protected function _processTitleCallback(Sabai_Request $request, Sabai_Application_Response $response, $route, $titleType)
    {
        // Change the callback plugin if the plugin of the route is different from that of the current plugin
        $plugin = $route['plugin'] !== $this->getPlugin()->name ? $this->getPlugin($route['plugin']) : $this->getPlugin();

        return $plugin->systemRoutableOnMainTitle($request, $response, $route['path'], $route['title'], $titleType);
    }

    protected function _getControllerClass($controllerName)
    {
        return 'Plugg_' . $this->getPlugin()->name . '_Controller_Main_' . $controllerName;
    }

    protected function _getControllerFile($controllerName)
    {
        return $this->getPlugin()->path . '/Controller/Main/' . str_replace('_', '/', $controllerName) . '.php';
    }
}
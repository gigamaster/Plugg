<?php
class Plugg_AdminController extends Plugg_RoutingController
{
    private $_accessCallbacks = array();

    public function __construct()
    {
        parent::__construct('Index', __CLASS__ . '_', dirname(__FILE__) . '/AdminController');
        $this->_contentName = 'admin';
        $this->addFilters(array('_filter'));
        $this->_defaultTabAjax = false;
    }

    protected function _getPluginRoutes($pluginPath)
    {
        return $this->getPlugin('System')->getRoutes($pluginPath, true);
    }

    protected function _processAccessCallback(Sabai_Request $request, Sabai_Application_Response $response, $route)
    {
        // Make sure the callback is called only once for each path
        $path = $route['path'];
        if (!isset($this->_accessCallbacks[$path])) {
            // Change the callback plugin if the plugin of the route is different from that of the current plugin
            $plugin = $route['plugin'] !== $this->getPlugin()->name ? $this->getPlugin($route['plugin']) : $this->getPlugin();
            $this->_accessCallbacks[$path] = $plugin->systemRoutableOnAdminAccess($request, $response, $path);
        }

        return $this->_accessCallbacks[$path];
    }

    protected function _processTitleCallback(Sabai_Request $request, Sabai_Application_Response $response, $route, $titleType)
    {
        // Change the callback plugin if the plugin of the route is different from that of the current plugin
        $plugin = $route['plugin'] !== $this->getPlugin()->name ? $this->getPlugin($route['plugin']) : $this->getPlugin();

        return $plugin->systemRoutableOnAdminTitle($request, $response, $route['path'], $route['title'], $titleType);
    }

    protected function _getControllerClass($controllerName)
    {
        return 'Plugg_' . $this->getPlugin()->name . '_Controller_Admin_' . $controllerName;
    }

    protected function _getControllerFile($controllerName)
    {
        return $this->getPlugin()->path . '/Controller/Admin/' . str_replace('_', '/', $controllerName) . '.php';
    }

    protected function _filterBeforeFilter(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!$this->getUser()->isAuthenticated()) {
            // Redirect to the user plugin login page
            $response->setLoginRequiredError();
            $response->send($request);
            exit;
        }

        if (!$this->getUser()->isSuperUser()) {
            $response->setError(
                $this->_('Access denied'),
                array('script' => 'main', 'base' => '/')
            );
            $response->send($request);
            exit;
        }
    }

    protected function _filterAfterFilter(Sabai_Request $request){}
}
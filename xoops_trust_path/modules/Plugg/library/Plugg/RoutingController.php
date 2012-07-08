<?php
abstract class Plugg_RoutingController extends Sabai_Application_RoutingController
{
    protected $_defaultTabTitle;
    protected $_eventNamePrefix;

    private $_tabSetAdded = 0;
    private $_pageInfoAdded = 0;
    private $_pageMenuAdded = false;

    protected function __construct($defaultController, $controllerPrefix, $controllerDir, array $defaultControllerArgs = array(), $defaultControllerFile = null)
    {
        parent::__construct($controllerPrefix, $controllerDir, $defaultController, $defaultControllerArgs, $defaultControllerFile);
        $this->setFilters(array('_global'));
        $this->_eventNamePrefix = str_replace('_', '', substr(get_class($this), 6)); // Remove plugg_
    }

    protected function _globalBeforeFilter(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->DispatchEvent($this->_eventNamePrefix . 'Enter', array($request, $response));
    }

    protected function _globalAfterFilter(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->DispatchEvent($this->_eventNamePrefix . 'Exit', array($request, $response));
    }

    public function forward($route, Sabai_Request $request, Sabai_Application_Response $response, $stackContentName = false)
    {
        if (0 !== strpos($route, '/')) {
            $route = $this->getUrlBase() . '/' . $route;
        }

        parent::forward($route, $request, $response, $stackContentName);
    }

    final protected function _doForward($route, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($this->_tabSetAdded) {
            // Remove tabs added by previous controller request
            do {
                $response->removeTabSet();
            } while (--$this->_tabSetAdded);
        } else {
            if ($this->_pageInfoAdded > 0) {
                // Remove page info added previously
                do {
                    $response->popPageInfo();
                } while (--$this->_pageInfoAdded);
            }
            if ($this->_pageMenuAdded) {
                // Reset page menu
                $response->popPageMenu();
            }
        }

        parent::_doForward($route, $request, $response);
    }

    protected function _isRoutable(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $routes_requested = explode('/', trim($this->getNextRoute(), '/') . '/');

        if (!$requested_root_route = array_shift($routes_requested)) return false;

        $requested_root_path = '/' . $requested_root_route;

        if (!$all_routes = $this->_getPluginRoutes($requested_root_path)) return false;

        $route_matched = $all_routes[$requested_root_path];

        if (!$requested_plugin = $this->getPlugin($route_matched['plugin'])) return false;

        $this->setCurrentPlugin($requested_plugin);
        $this->setUrlBase($requested_root_path);

        // Call access callback?
        if (!empty($route_matched['access_callback'])
            && !$this->_processAccessCallback($request, $response, $route_matched)
        ) {
            // Access denied. Set error if not already set
            if (!$response->isError()) $response->setError($this->_('Invalid request'));

            return false;
        }

        // Set page info of the route
        $route_matched['title'] = $this->_getTitle($request, $response, $route_matched);
        // Add page breadcrumb if the requested route is not the front page path
        if ($route_matched['title'] && $this->getPlugin('System')->getConfig('front_page', 'path') !== $requested_root_route) {
            $response->setPageInfo($route_matched['title'], $this->getUrl($requested_root_path));
            ++$this->_pageInfoAdded;
        }

        // Initialize some required variables
        $route_selected = $requested_root_route;
        $matched_routes = $tabs = $menu = $dynamic_route_keys = array();
        if (!empty($route_matched['controller']) || !empty($route_matched['forward'])) {
            $last_valid_route_matched = $route_matched;
        }

        while (($routes = @$route_matched['routes'])
            && null !== ($route_requested = array_shift($routes_requested))
        ) {
            foreach ($routes as $route_key => $route_path) {
                // Dynamic routes may not become a tab nor a menu unless menu_callback is defined
                if (0 === strpos($route_key, ':') && empty($route_data['menu_callback'])) continue;

                if (!isset($all_routes[$route_path])) continue;

                $route_data = $all_routes[$route_path];

                if ($route_data['type'] == Plugg::ROUTE_TAB) {
                    // Call access callback and/or check access permissions?
                    if ((!empty($route_data['access_callback']) && !$this->_processAccessCallback($request, $response, $route_data))
                        || (!empty($route_data['access_permissions']) && !$this->getUser()->hasPermission($route_data['access_permissions'])) 
                    ) {
                        // Access to this route not allowed
                        unset($routes[$route_key]);
                        continue;
                    }

                    // Access to tab allowed
                    $tabs[$route_data['weight'] + 1][$route_key] = array(
                        'title' => $this->_getTabTitle($request, $response, $route_data),
                        'url' => $this->getUrl($requested_root_path . '/' . (!empty($matched_routes) ? implode('/', $matched_routes) . '/' : '') . $route_key),
                        'ajax' => !empty($route_data['ajax']),
                    );
                } elseif ($route_data['type'] == Plugg::ROUTE_MENU) {
                    // Call access callback and/or check access permissions?
                    if ((!empty($route_data['access_callback']) && !$this->_processAccessCallback($request, $response, $route_data))
                        || (!empty($route_data['access_permissions']) && !$this->getUser()->hasPermission($route_data['access_permissions'])) 
                    ) {
                        // Access to this route not allowed
                        unset($routes[$route_key]);
                        continue;
                    }

                    // Add menu
                    $menu[$route_data['weight']][] = array(
                        'title' => $this->_getMenuTitle($request, $response, $route_data),
                        'url' => $this->getUrl($requested_root_path . '/' . (!empty($matched_routes) ? implode('/', $matched_routes) . '/' : '') . $route_key),
                        'ajax' => !empty($route_data['ajax']),
                    );
                }
            }

            if (!empty($tabs)) {
                $tabs[0][''] = array(
                    'title' => ($tab_title = $this->_getDefaultTabTitle($request, $response, $route_matched)) ? $tab_title : $this->_('Top'),
                    'url' => $this->getUrl($requested_root_path . '/' .  implode('/', $matched_routes)),
                );
            }

            // Some access callbacks set the response status as error, but the status should not be changed here since
            // we are now just checking whether or not the route is accessible, not really trying to access the route.
            $response->setStatus(Sabai_Application_Response::VIEW)->clearMessages();

            // Default route?
            if ($route_requested == '') {
                if ($this->_addTabSet($request, $response, $tabs)) {
                    $response->setCurrentTab('');
                    $response->setPageInfo($tabs[0]['']['title'], $tabs[0]['']['url']);
                }
                $this->_addPageMenu($request, $response, $menu);
                
                break;
            }

            if (isset($routes[$route_requested])) {
                $route_selected = $route_requested;
                $route_matched = $all_routes[$routes[$route_requested]];
            } else {
                $matched = false;
                // Check if dynamic routes are defined and any matching route
                krsort($routes, SORT_STRING);
                foreach ($routes as $route_key => $route_path) {
                    if (0 === strpos($route_key, ':')) {
                        if (!isset($all_routes[$route_path])) continue;

                        $route_data = $all_routes[$route_path];

                        if (!empty($route_data['format'][$route_key])) {
                            $regex = '(' . str_replace('#', '\#', $route_data['format'][$route_key]) . ')';
                        } elseif ($route_key == ':controller') {
                            $regex = !empty($route_data['controller']) ? $route_data['controller'] : '(' . $this->_controllerRegex . ')';
                        } else {
                            $regex = '([a-z0-9~\s\.:_\-]+)';
                        }
                        if (!preg_match('#^' . $regex . '#i', $route_requested, $matches)) continue;

                        if ($route_key == ':controller') {
                            if (!isset($route_data['controller'])) $route_data['controller'] = $matches[0];
                        } else {
                            $request->set(substr($route_key, 1), $matches[0]);
                            $dynamic_route_keys[$route_key] = $matches[0];
                        }
                        $route_selected = $route_key;
                        $route_matched = $route_data;
                        $matched = true;
                        break;
                    }
                }

                if (!$matched) {
                    if ($this->_addTabSet($request, $response, $tabs)) $response->setCurrentTab('');
                    $this->_addPageMenu($request, $response, $menu);
                    break;
                }
            }

            // Call access callback and/or check access permissions?
            if ((!empty($route_matched['access_callback']) && !$this->_processAccessCallback($request, $response, $route_matched))
                || (!empty($route_matched['access_permissions']) && !$this->getUser()->hasPermission($route_matched['access_permissions'])) 
            ) {
                // Access denied. Set error if not already set
                if (!$response->isError()) $response->setError($this->_('Invalid request'));

                return false;
            }

            if ($route_matched['type'] !== Plugg::ROUTE_CALLBACK) {

                if (empty($route_matched['no_breadcrumb'])) {
                    $breadcrumbs = array();

                    if ($title = $this->_getTitle($request, $response, $route_matched)) {
                        $breadcrumbs[] = array(
                            'title' => $title,
                            'url' => $this->getUrl($requested_root_path . '/' . (!empty($matched_routes) ? implode('/', $matched_routes) . '/' : '') . $route_requested),
                            'ajax' => !empty($route_matched['ajax']),
                        );
                    }

                    if ($this->_addTabSet($request, $response, $tabs)) {
                        $current_tab = $route_selected;
                        // Resolve current tab if requested route is not a tab
                        if ($route_matched['type'] != Plugg::ROUTE_TAB) {
                            // Set the default tab as the current tab
                            $current_tab = '';

                            // Add breadcrumb for the default tab
                            $breadcrumbs[] = array(
                                'title' => $tabs[0]['']['title'],
                                'url' => $tabs[0]['']['url'],
                                'ajax' => !empty($tabs[0]['']['ajax']),
                            );
                        }
                        $response->setCurrentTab($current_tab);
                    }

                    foreach (array_reverse($breadcrumbs) as $bc) {                        
                        $response->setPageInfo($bc['title'], $bc['url'], $bc['ajax']);
                        ++$this->_pageInfoAdded;
                    }
                } else {
                    if ($this->_addTabSet($request, $response, $tabs)) {
                        $current_tab = $route_matched['type'] != Plugg::ROUTE_TAB ? '' : $route_selected;
                        $response->setCurrentTab($current_tab);
                    }
                }

                // Clear
                $tabs = $menu = array();
            }

            $selected_routes[] = $route_selected;
            $matched_routes[] = $route_requested;

            if (!empty($route_matched['controller']) || !empty($route_matched['forward'])) {
                $last_valid_route_matched = $route_matched;
            }
        }

        // Any valid route has matched?
        if (empty($last_valid_route_matched)) return false;

        // We don't need routes data anymore, save memory
        unset($last_valid_route_matched['routes']);

        if ($last_valid_route_matched['plugin'] !== $this->getPlugin()->name) {
            $this->setCurrentPlugin($this->getPlugin($last_valid_route_matched['plugin']));
        }

        if (!empty($matched_routes)) {
            $path_matched = $requested_root_path . '/' . implode('/', $matched_routes);
            $this->setUrlBase(dirname($path_matched));
        } else {
            $path_matched = $requested_root_path;
        }

        if (!empty($last_valid_route_matched['forward'])) {
            // Convert dynamic route parts to actual values
            $last_valid_route_matched['forward'] = strtr($last_valid_route_matched['forward'], $dynamic_route_keys);
            if (0 !== strpos($last_valid_route_matched['forward'], '/')) {
                // Convert forwarding route to full path if relative path
                $last_valid_route_matched['forward'] = $this->getUrlBase() . '/' . $last_valid_route_matched['forward'];
            }
        } else {
            $controller_name = $last_valid_route_matched['controller'];
            $last_valid_route_matched['controller_file'] = $this->_getControllerFile($controller_name);
            $last_valid_route_matched['controller'] = $this->_getControllerClass($controller_name);
        }

        return new Plugg_RoutingControllerRoute($path_matched . '/', $route_selected, $last_valid_route_matched);
    }

    abstract protected function _getPluginRoutes($pluginPath);
    abstract protected function _processAccessCallback(Sabai_Request $request, Sabai_Application_Response $response, $routeData);
    abstract protected function _processTitleCallback(Sabai_Request $request, Sabai_Application_Response $response, $routeData, $titleType);
    abstract protected function _getControllerClass($controllerName);
    abstract protected function _getControllerFile($controllerName);

    private function _getTitle($request, $response, $routeData)
    {
        if (empty($routeData['title_callback'])) return @$routeData['title'];

        return $this->_processTitleCallback($request, $response, $routeData, Plugg::ROUTE_TITLE_NORMAL);
    }

    private function _getTabTitle($request, $response, $routeData)
    {
        if (empty($routeData['title_callback'])) return $routeData['title'];

        return $this->_processTitleCallback($request, $response, $routeData, Plugg::ROUTE_TITLE_TAB);
    }

    private function _getMenuTitle($request, $response, $routeData)
    {
        if (empty($routeData['title_callback'])) return $routeData['title'];

        return $this->_processTitleCallback($request, $response, $routeData, Plugg::ROUTE_TITLE_MENU);
    }

    private function _getDefaultTabTitle($request, $response, $routeData)
    {
        if (empty($routeData['title_callback'])) return @$routeData['title'];

        return $this->_processTitleCallback($request, $response, $routeData, Plugg::ROUTE_TITLE_TAB_DEFAULT);
    }

    private function _addTabSet($request, $response, $tabs)
    {
        ksort($tabs);
        $tab_set = array();
        foreach (array_keys($tabs) as $tab_weight) {
            foreach ($tabs[$tab_weight] as $tab_name => $tab_data) {
                $tab_set[$tab_name] = $tab_data;
            }
        }

        if (count($tab_set) <= 1) return false;

        // Add tabs
        $response->addTabSet($tab_set);
        ++$this->_tabSetAdded;

        return true;
    }

    private function _addPageMenu($request, $response, $menu)
    {
        ksort($menu);
        $menu_set = array();
        foreach (array_keys($menu) as $menu_weight) {
            foreach ($menu[$menu_weight] as $menu_data) {
                $menu_set[] = $menu_data;
            }
        }

        if (empty($menu_set)) return false;

        $response->setPageMenu($menu_set);
        $this->_pageMenuAdded = true;
    }
}
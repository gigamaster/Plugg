<?php
class Plugg_System_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_System_Routable_Admin, Plugg_System_Report
{
    const CONFIG_CATEGORY_GENERAL = 0, CONFIG_CATEGORY_SEO = 1;

    const STATUS_SEVERITY_OK = 'success', STATUS_SEVERITY_WARNING = 'warning', STATUS_SEVERITY_ERROR = 'error';

    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/system' => array(
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/system/minify/css' => array(
                'controller' => 'MinifyCss',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/system/minify/js' => array(
                'controller' => 'MinifyJs',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path){}

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType){}

    /* End implementation of Plugg_System_Routable_Main */

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/system' => array(
                'controller' => 'Index',
                'title' => $this->_('System management'),
                'type' => Plugg::ROUTE_TAB,
                'title_callback' => true,
            ),
            '/system/php' => array(
                'controller' => 'Php',
                'title' => $this->_('PHP information'),
            ),
            '/system/plugins' => array(
                'controller' => 'Plugins',
                'title' => $this->_('Plugins'),
                'type' => Plugg::ROUTE_TAB,
            ),
            '/system/plugins/plugin/:plugin_name' => array(
                'controller' => 'Plugins_Plugin',
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/system/plugins/refresh' => array(
                'controller' => 'Plugins_Refresh',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Refresh list'),
            ),
            '/system/minify/css' => array(
                'controller' => 'MinifyCss',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/system/minify/js' => array(
                'controller' => 'MinifyJs',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/system/settings' => array(
                'controller' => 'Settings',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Site settings'),
                'title_callback' => true,
                'weight' => 30,
            ),
            '/system/settings/seo' => array(
                'controller' => 'Settings_Seo',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('SEO'),
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/system/plugins/plugin/:plugin_name':
                // Make sure the requested plugin exists
                return ($plugin_name = $request->asStr('plugin_name'))
                    && ($this->_application->getPlugin($plugin_name));
        }
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/system':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Site information') : $title;
            case '/system/settings':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('General') : $title;
            case '/system/plugins/plugin/:plugin_name':
                return $this->_application->getPlugin($request->asStr('plugin_name'))->nicename;
        }
    }

    /* End implementation of Plugg_System_Routable_Admin */


    /* Start implementation of Plugg_System_Report */

    public function systemReportGetNames()
    {
        return array('Plugg', 'PHP', 'PHP memory_limit', 'PHP register_globals', 'PHP display_errors',
            'PHP allow_url_fopen', 'PHP allow_url_include', 'PHP mbstring', 'File system', 'Database'
        );
    }

    public function systemReportGetStatus($reportName)
    {
        switch ($reportName) {
            case 'Plugg':
                return array('title' => $reportName, 'value' => Plugg::VERSION, 'weight' => -1);

            case 'PHP':
                if (version_compare(PHP_VERSION, Plugg::PHP_VERSION_MIN, '<')) {
                    $severity = self::STATUS_SEVERITY_ERROR;
                    $description = sprintf($this->_('Plugg requires PHP version %s or higher to run properly.'), Plugg::PHP_VERSION_MIN);
                } else {
                    $severity = self::STATUS_SEVERITY_OK;
                    $description = '';
                }

                return array(
                    'title' => $reportName,
                    'value' => $this->_application->LinkTo(PHP_VERSION, $this->getUrl('php'), array('target' => '_blank')),
                    'severity' => $severity,
                    'description' => $description,
                    'weight' => 2,
                );

            case 'PHP memory_limit':
                return array('title' => $reportName, 'value' => ini_get('memory_limit'), 'weight' => 4);

            case 'PHP register_globals':
                if ($this->_isIniSettingEnabled('register_globals')) {
                    return array(
                        'title' => $reportName,
                        'value' => $this->_('Enabled'),
                        'severity' => self::STATUS_SEVERITY_WARNING,
                        'description' => sprintf($this->_('For security reasons, it is highly recommended that the PHP %s setting be disabled.'), $reportName),
                        'weight' => 6,
                    );
                }
                return array(
                    'title' => $reportName,
                    'value' => $this->_('Disabled'),
                    'weight' => 6,
                );

            case 'PHP display_errors':
                if ($this->_isIniSettingEnabled('display_errors')) {
                    return array(
                        'title' => $reportName,
                        'value' => $this->_('Enabled'),
                        'severity' => self::STATUS_SEVERITY_WARNING,
                        'description' => sprintf($this->_('For security reasons, it is highly recommended that the PHP %s setting be disabled.'), $reportName),
                        'weight' => 8,
                    );
                }
                return array(
                    'title' => $reportName,
                    'value' => $this->_('Disabled'),
                    'weight' => 8,
                );

            case 'PHP allow_url_fopen':
                if ($this->_isIniSettingEnabled('allow_url_fopen')) {
                    return array(
                        'title' => $reportName,
                        'value' => $this->_('Enabled'),
                        'severity' => self::STATUS_SEVERITY_WARNING,
                        'description' => sprintf($this->_('For security reasons, it is highly recommended that the PHP %s setting be disabled.'), $reportName),
                        'weight' => 10,
                    );
                }
                return array(
                    'title' => $reportName,
                    'value' => $this->_('Disabled'),
                    'weight' => 10,
                );
                
            case 'PHP allow_url_include':
                if ($this->_isIniSettingEnabled('allow_url_include')) {
                    return array(
                        'title' => $reportName,
                        'value' => $this->_('Enabled'),
                        'severity' => self::STATUS_SEVERITY_WARNING,
                        'description' => sprintf($this->_('For security reasons, it is highly recommended that the PHP %s setting be disabled.'), $reportName),
                        'weight' => 12,
                    );
                }
                return array(
                    'title' => $reportName,
                    'value' => $this->_('Disabled'),
                    'weight' => 12,
                );

            case 'PHP mbstring':
                $title = $this->_('PHP mbstring extension');
                if (!function_exists('mb_detect_order')) {
                    return array(
                        'title' => $title,
                        'value' => $this->_('Disabled'),
                        'severity' => self::STATUS_SEVERITY_WARNING,
                        'description' => $this->_('It is recommended that the PHP mbstring extension be enbaled for Plugg to run properly.'),
                        'weight' => 14,
                    );
                }
                return array(
                    'title' => $title,
                    'value' => $this->_('Enabled'),
                    'weight' => 14,
                );

            case 'File system':
                $title = $this->_('File system');
                $description = array();
                foreach (array(Plugg::$cacheDir) as $dir) {
                    if (!is_writable($dir) && !@chmod($dir, 0777)) {
                        $description[] = $dir;
                    }
                }
                
                $plugin_manager = $this->_application->getPluginManager();
                foreach (array_keys($plugin_manager->getInstalledPlugins()) as $plugin_name) {
                    if (!$plugin_info = $plugin_manager->getLocalPlugin($plugin_name)) continue;

                    if (!empty($plugin_info['chmod'])) {
                        foreach ((array)$plugin_info['chmod'] as $file) {
                            if (false !== strpos($file, '/') || false !== strpos($file, DIRECTORY_SEPARATOR)) {
                                $file = Plugg::$pluginDir . '/' . $plugin_name . $file;
                                if (file_exists($file)) {
                                    if (!is_writable($file) && !@chmod($file, is_dir($file) ? 0777 : 0666)) {
                                        $description[] = $file;
                                    }
                                } else {
                                    $description[] = $file;
                                }
                            } else {
                                // Fetch file path from plugin configuration parameters
                                if (($plugin = $plugin_manager->getPlugin($plugin_name))
                                    && ($path = $plugin->getConfig(explode(',', $file)))
                                    && false !== strpos($path, '/')
                                    && false !== strpos($path, DIRECTORY_SEPARATOR)
                                ) {
                                    if (file_exists($path)) {
                                        if (!is_writable($path) && !@chmod($path, is_dir($path) ? 0777 : 0666)) {
                                            $description[] = $path;
                                        }
                                    } else {
                                        $description[] = $path;
                                    }
                                }
                            }
                        }
                    }
                }
                
                if (empty($description)) {
                    return array('title' => $title, 'value' => $this->_('Configured correctly'), 'weight' => 0);
                }
                array_unshift($description, $this->_('The following directories/files need to be writable by the web server:'));

                return array(
                    'title' => $title,
                    'value' => $this->_('Not configured correctly'),
                    'severity' => self::STATUS_SEVERITY_ERROR,
                    'description' => implode('<br />', $description),
                    'weight' => 1,
                );
                
            case 'Database':
                $db = $this->getDB();
                $db_scheme = $db->getScheme();
                $constant = 'Plugg::' . strtoupper($db_scheme) . '_VERSION_RECOMMENDED';
                if (defined($constant) && $db->checkVersion(constant($constant), '<')) {
                    $severity = self::STATUS_SEVERITY_WARNING;
                    $description = sprintf($this->_('%s version %s or higher is recommended.'), $db_scheme, constant($constant));
                } else {
                    $severity = self::STATUS_SEVERITY_OK;
                    $description = '';
                }

                return array(
                    'title' => $this->_('Database'),
                    'value' => sprintf('%s-%s', $db_scheme, $db->getVersion(false)),
                    'severity' => $severity,
                    'description' => $description,
                    'weight' => 20,
                );
        }
    }
    
    private function _isIniSettingEnabled($setting)
    {
        return ($value = ini_get($setting)) && strcasecmp(trim($value), 'off') !== 0;
    }

    /* End implementation of Plugg_System_Report */


    public function onSystemRoutableMainInstalled($pluginEntity, $plugin)
    {
        $this->_onSystemRoutableInstalled($plugin, false);
    }

    public function onSystemRoutableAdminInstalled($pluginEntity, $plugin)
    {
        $this->_onSystemRoutableInstalled($plugin, true);
    }

    private function _onSystemRoutableInstalled($plugin, $admin = false)
    {   
        $model = $this->getModel();
        if ($admin) {
            $routes = $plugin->systemRoutableGetAdminRoutes();
            $entity_name = 'Adminroute';
        } else {
            $routes = $plugin->systemRoutableGetMainRoutes();
            $entity_name = 'Route';
        }

        // Insert route data
        foreach ($routes as $route_path => $route_data) {
            $route_path = strtolower(rtrim(trim($route_path), '/'));

            // Is it a child route of another plugin?
            if (0 !== strpos($route_path, '/' . strtolower($plugin->name))) {
                // If so, the route may not be a root route so the route string must contain more than 1 slash
                if (!$second_slash_pos = strpos($route_path, '/', 1)) continue; // invalid route path
            }

            $route = $model->create($entity_name);
            $route->markNew();
            $route->controller = (string)@$route_data['controller'];
            $route->forward = (string)@$route_data['forward'];
            $route->plugin = $plugin->name;
            $route->type = isset($route_data['type']) ? $route_data['type'] : Plugg::ROUTE_NORMAL;
            $route->path = $route_path;
            $route->title = (string)@$route_data['title'];
            $route->format = serialize((array)@$route_data['format']);
            $route->access_callback = !empty($route_data['access_callback']) ? 1 : 0;
            $route->title_callback = !empty($route_data['title_callback']) ? 1 : 0;
            $route->weight = (int)@$route_data['weight'];
            $route->depth = substr_count($route_path, '/');
        }

        $model->commit();
        
        $this->removeCache(); // clear cached route data
    }

    public function onSystemRoutableMainUninstalled($pluginEntity, $plugin)
    {
        $this->_onSystemRoutableUninstalled($plugin, false);
    }

    public function onSystemRoutableAdminUninstalled($pluginEntity, $plugin)
    {
        $this->_onSystemRoutableUninstalled($plugin, true);
    }

    private function _onSystemRoutableUninstalled($plugin, $admin = false)
    {
        $model = $this->getModel();
        $entity_name = $admin ? 'Adminroute' : 'Route';
        $criteria = $model->createCriteria($entity_name)->plugin_is($plugin->name);
        $model->getGateway($entity_name)->deleteByCriteria($criteria);
        
        $this->removeCache(); // remove cached route data
    }

    public function onSystemRoutableMainUpgraded($pluginEntity, $plugin)
    {
        $this->_onSystemRoutableUninstalled($plugin, false);
        $this->_onSystemRoutableInstalled($plugin, false);
    }

    public function onSystemRoutableAdminUpgraded($pluginEntity, $plugin)
    {
        $this->_onSystemRoutableUninstalled($plugin, true);
        $this->_onSystemRoutableInstalled($plugin, true);
    }
    
    public function onPluggInit()
    {
        // Set mod_rewrite options if enabled
        if (($mod_rewrite = $this->getConfig('mod_rewrite'))
            && $mod_rewrite['enable']
            && $mod_rewrite['format']
        ) {
            $this->_application->setModRewriteFormat($mod_rewrite['format'], 'main');
        }
    }

    public function onPluggRun($controller, $request, $response)
    {
    // Enable debug?
        $debug_level = $this->getConfig('debug', 'level');
        Sabai_Log::level($debug_level);
        if (Sabai_Log::NONE != $debug_level) { 
            switch ($this->getConfig('debug', 'output')) {
                case 'html':
                    require_once 'Sabai/Log/Writer/HTML.php';
                    Sabai_Log::writer(new Sabai_Log_Writer_HTML());
                    break;
                case 'firebug':
                    break;
            }
        }
        if ($this->getConfig('disableCssCache')
            || (!$last_update = $this->_application->getPluginManager()->getPluginsLastLoaded())
        ) {
            $last_update = time();
        }
        $response->addCssFile($this->_application->createUrl(array(
            'script' => $this->_application->getCurrentScriptName() == 'main' ? 'main' : 'admin',
            'base' => '/system',
            // Append the timestamp of when plugins were last updated and cached
            'path' => 'minify/css/' . $last_update
        )));
    }
    
    public function onMainControllerEnter($request, $response)
    {
    
        if ($this->getConfig('display', 'breadcrumbs', 'show_full_path')) {
            $response->setPageInfo($this->_('Home'), $this->_application->SiteUrl());
        }
        $response->setPageInfo(
            $this->_application->getTitle(),
            $this->_application->createUrl(array('base' => '/', 'mod_rewrite' => false))
        );
        
        // Display breadcrumbs?
        if (!$this->getConfig('display', 'breadcrumbs', 'show')) {
            $response->setBreadcrumbsEnabled(false);
        }
    }

    public function getRoutes($rootPath = '/', $admin = false)
    {
        $cache_id = str_replace('/', '_', $admin ? 'routes_admin' . $rootPath : 'routes' . $rootPath);
        if ($cache = $this->getCache($cache_id)) return unserialize($cache);

        $model = $this->getModel();
        $repository = $admin ? $model->Adminroute : $model->Route;
        $routes = $repository->criteria()->path_startsWith($rootPath)->fetch();

        if (!$routes->count()) return false; // no routes

        $ret = array();
        $root_path_dir = dirname($rootPath);
        foreach ($routes as $route) {
            // Initialize route data
            // Any child route data already defined?
            $child_routes = !empty($ret[$route->path]['routes']) ? $ret[$route->path]['routes'] : array();
            $ret[$route->path] = $route->toArray();
            $ret[$route->path]['routes'] = $child_routes;

            $current_path = $route->path;
            while ($root_path_dir !== $parent_path = dirname($current_path)) {
                $current_base = substr($current_path, strlen($parent_path) + 1); // remove the parent path part

                if (!isset($ret[$current_path]['path'])) {
                    // Check whether format is defined if dynamic route
                    $format = array();
                    if (0 === strpos($current_base, ':') && isset($ret[$route->path]['format'][$current_base])) {
                        $format = $ret[$route->path]['format'][$current_base];
                        unset($ret[$route->path]['format'][$current_base]);
                    }
                    $ret[$current_path]['path'] = $current_path;
                    $ret[$current_path]['plugin'] = $route->plugin;
                    $ret[$current_path]['type'] = Plugg::ROUTE_NORMAL;
                    $ret[$current_path]['format'] = !empty($format) ? array($current_base => $format) : array();
                }
                if (!isset($ret[$parent_path]['plugin'])) $ret[$parent_path]['plugin'] = $route->plugin;
                $ret[$parent_path]['routes'][$current_base] = $current_path;

                $current_path = $parent_path;
            }
        }
        $this->saveCache(serialize($ret), $cache_id);

        return $ret;
    }

    public function isPluginInstallable($name, &$error)
    {
        // Check if plugin name is reserved
        $reserved_plugin_names = $this->_getReservedPluginNames(array($this->_application->getName()));
        if (in_array(strtolower($name), array_map('strtolower', $reserved_plugin_names))) {
            $error = sprintf($this->_('Plugin name %s is reserved by the system.'), $name);
            return false;
        }

        // Is plugin installed already?
        if ($this->_application->getPluginManager()->isPluginInstalled($name)) {
            $error = sprintf($this->_('Plugin with the name %s is installed already.'), $name);
            return false;
        }

        // Plugin files exist?
        if (!$data = $this->_application->getPluginManager()->getLocalPlugin($name)) {
            $error = $this->_('Invalid plugin.');
            return false;
        }

        // Check if meets requirements
        if (!empty($data['supported_plugg_types'])) {
            $applications = 0;
            foreach ($data['supported_plugg_types'] as $application) {
                $constant = 'Plugg::' . strtoupper($application);
                if (defined($constant)) $applications = $applications | constant($constant); 
            }
            if (!($this->_application->getType() & $applications)) {
                $error = $this->_('The selected plugin is not compatible with this application.');
                return false;
            }
        }
        if (!empty($data['required_php_version']) && version_compare(phpversion(), $data['required_php_version'], '<')) {
            $error = sprintf($this->_('The selected plugin requires PHP %s or higher'), $data['required_php_version']);
            return false;
        }
        if (!empty($data['required_php_extensions'])) {
            foreach ($data['required_php_extensions'] as $extension) {
                if (!extension_loaded($extension)) {
                    $error = sprintf($this->_('The selected plugin requires the PHP %s extension to be enabled on your server.'), $extension);
                    return false;
                }
            }
        }
        if (!empty($data['required_php_libraries'])) {
            foreach ($data['required_php_libraries'] as $lib) {
                $lib_file = str_replace('_', DIRECTORY_SEPARATOR, $lib) . '.php';
                if (!is_includable($lib_file)) {
                    $error = sprintf($this->_('The selected plugin requires the %s library file(s) to be available under include_path: %s'), $lib, get_include_path());
                    return false;
                }
            }
        }
        if (!empty($data['supported_database_systems'])) {
            if (!in_array(strtolower($this->getDB()->getScheme()), array_map('strtolower', $data['supported_database_systems']))) {
                $error = $this->_('The selected plugin requires a %s database.', implode('/', $db_scheme_supported));
                return false;
            }
        }
        if (!empty($data['required_plugins'])) {
            $plugins_installed = $this->_application->getPluginManager()->getInstalledPlugins();
            foreach ($data['required_plugins'] as $plugin_required) {
                @list($required_plugin_name, $required_plugin_version) = explode('-', $plugin_required);
                if (!array_key_exists($required_plugin_name, $plugins_installed)) {
                    $error = sprintf($this->_('The selected plugin requires plugin %s to be installed.'), $required_plugin_name);
                    return false;
                }
                if (isset($required_plugin_version)) {
                    if (version_compare($plugins_installed[$required_plugin_name]['version'], $required_plugin_version, '<')) {
                        $error = sprintf($this->_('The selected plugin requires plugin %s version %s or higher to be installed.'), $required_plugin_name, $required_plugin_version);
                        return false;
                    }
                }
            }
        }

        return $data;
    }

    private function _getReservedPluginNames(array $pluginNames = array())
    {
        foreach (glob($this->_application->getPath() . '/*.php') as $file) {
            $pluginNames[] = basename($file, '.php');
        }
        foreach (glob($this->_application->getPath() . '/*', GLOB_ONLYDIR) as $dir) {
            $pluginNames[] = $dir;
        }

        return $pluginNames;
    }

    public function isPluginInstalled($pluginName)
    {
        return $this->getModel()->Plugin->criteria()->name_is($pluginName)->fetch()->getFirst();
    }

    public function installPlugin($name, $data, $params = array(), $priority = 0)
    {
        $model = $this->getModel();
        
        $entity = $model->create('Plugin');
        $entity->name = $name;
        $entity->locked = !$data['uninstallable'];
        $entity->setParams($params, array(), false);
        $entity->version = $data['version'];
        $entity->priority = $priority > 99 ? 99 : $priority;
        $entity->nicename = $data['title'];
        $entity->markNew();
        
        if (!empty($data['required_plugins'])) {
            foreach ($model->Plugin->criteria()->name_in($data['required_plugins'])->fetch() as $plugin) {
                $dep = $entity->createDependency();
                $dep->requires = $plugin->id;
                $dep->markNew();
            }
        }
        
        if ($model->commit()) {
            $this->_application->getPluginManager()->reloadPlugins();
            $message = '';
            if ($this->_application->getPlugin($entity->name)->install($message)) {
                return $entity;
            } else {
                $entity->markRemoved();
                if (!$entity->commit()) {
                    $message .= ' ' . $this->_('Additionally, failed deleting plugin data from the database. Please uninstall the plugin manually.');
                }
                $this->_application->getPluginManager()->reloadPlugins();
            }
        } else {
            $message = $this->_('Failed inserting plugin data into the database. ' . $model->getCommitError());
        }
        
        return $message;
    }
    
    public function isPluginUninstallable($name, &$error)
    {
        if (!$entity = $this->isPluginInstalled($name)) {
            $error = sprintf($this->_('Plugin %s is not installed.'), $name);
            
            return false;
        }

        if (!$plugin_data = $this->_application->getPluginManager()->getLocalPlugin($name)) {
            $error = $this->_('Invalid plugin');

            return false;
        }
        
        if (!$plugin_data['uninstallable']) {
            $error = $this->_('The selected plugin may not be uninstalled.');

            return false;
        }
        
        if ($plugins = $entity->isRequiredByOtherPlugins()) {
            foreach ($plugins as $plugin) {
                $plugin_names = $plugin->nicename;
            }
            $error = sprintf($this->_('The selected plugin is required by %s'), implode(', ', array_keys($plugin_names)));

            return false;
        }
        
        return true;
    }
    
    public function uninstallPlugin($name)
    {
        if (!$entity = $this->getModel()->Plugin->criteria()->name_is($name)->fetch()->getFirst()) {
            return $this->_('Failed fetching plugin data from the database.');
        }
        
        $entity->markRemoved();
        if (!$entity->commit()) {
            return $this->_('Failed deleting plugin data from the database.');
        }
        
        $message = '';
        if (!$this->_application->getPlugin($name)->uninstall($message)) {
            return $message;
        }
        
        return $entity;
    }
    
    public function upgradePlugin($name, $data)
    {
        if (!$entity = $this->getModel()->Plugin->criteria()->name_is($name)->fetch()->getFirst()) {
            return $this->_('Failed fetching plugin data from the database.');
        }
        
        if (!$plugin = $this->_application->getPlugin($name)) {
            return $this->_('Invalid plugin.');
        }
        
        $message = '';
        if (false === $version = $plugin->upgrade($entity->version, $message)) {
            return $message;
        }

        $entity->locked = !$data['uninstallable'];
        $entity->setParams(array_merge($plugin->getDefaultConfig(), $entity->getParams()), array(), false);
        $entity->version = $data['version'];
        $entity->nicename = $data['title'];
        if (!$entity->commit()) {
            return $this->_('Failed updating plugin data in the database.');
        }
        
        return $entity;
    }

    public function onSystemAdminPluginInstalled($pluginEntity, ArrayObject $log = null)
    {
        if (($plugin = $this->_application->getPlugin($pluginEntity->name))) {
            if ($default_config = $plugin->getDefaultConfig()) {
                $pluginEntity->setParams(array_merge($default_config, $pluginEntity->getParams()), array(), false);
                $pluginEntity->commit();
            }
        
            if ($interfaces = class_implements($plugin)) {
                // Dispatch installed event for each interface
                foreach ($interfaces as $interface) {
                    if (stripos($interface, 'plugg_') == 0) {
                        $event = str_replace('_', '', substr($interface, 6))  . 'Installed'; // Remove the plugg_ prefix
                        $this->_application->DispatchEvent($event, array($pluginEntity, $plugin, $log));
                    }
                }
            }
        }
    }

    public function onSystemAdminPluginUninstalled($pluginEntity, ArrayObject $log = null)
    {
        if (($plugin = $this->_application->getPlugin($pluginEntity->name)) &&
            ($interfaces = class_implements($plugin)) // get interfaces implemented by the plugin
        ) {
            // Dispatch uninstalled event for each interface
            foreach ($interfaces as $interface) {
                if (stripos($interface, 'plugg_') == 0) {
                    $event = str_replace('_', '', substr($interface, 6))  . 'Uninstalled'; // Remove the plugg_ prefix
                    $this->_application->DispatchEvent($event, array($pluginEntity, $plugin, $log));
                }
            }
        }
    }

    public function onSystemAdminPluginUpgraded($pluginEntity, ArrayObject $log = null)
    {
        if (($plugin = $this->_application->getPlugin($pluginEntity->name)) &&
            ($interfaces = class_implements($plugin, false)) // get interfaces implemented by the plugin
        ) {
            // Dispatch upgraded event for each interface
            foreach ($interfaces as $interface) {
                if (stripos($interface, 'plugg_') == 0) {
                    $event = str_replace('_', '', substr($interface, 6))  . 'Upgraded'; // Remove the plugg_ prefix and underscores
                    $this->_application->DispatchEvent($event, array($pluginEntity, $plugin, $log));
                }
            }
        }
    }

    public function onSystemAdminPluginConfigured($pluginEntity, $paramsOld, ArrayObject $log = null)
    {
        if (($plugin = $this->_application->getPlugin($pluginEntity->name)) &&
            ($interfaces = class_implements($plugin, false)) // get interfaces implemented by the plugin
        ) {
            // Dispatch configured event for each interface
            foreach ($interfaces as $interface) {
                if (stripos($interface, 'plugg_') == 0) {
                    $event = str_replace('_', '', substr($interface, 6))  . 'Configured'; // Remove the plugg_ prefix
                    $this->_application->DispatchEvent($event, array($pluginEntity, $plugin, $paramsOld, $log));
                }
            }
        }
    }

    public function sendMinifiedCss($getContentFunc)
    {
        $cache_limit = 4320000;
        $last_modified = $this->_application->getPluginManager()->getPluginsLastLoaded();
        
        set_include_path($this->_path . '/lib' . PATH_SEPARATOR . get_include_path());
        require_once 'Minify.php';
        Minify::setCache(Plugg::$cacheDir);

        // Layout CSS file
        //$layout_css_dir = $response->getLayoutDir() . '/css';
        //$src1 = new Minify_Source(array(
        //    'filepath' => $admin ? $layout_css_dir . '/admin.css' : $layout_css_dir . '/main.css',
        //    'minifyOptions' => array(
        //        'currentDir' => $layout_css_dir,
        //    )
        //));

        // Other CSS file contents to minify
        $current_script = $this->_application->getScript($this->_application->getCurrentScriptName());
        if (false !== $pos = strpos($current_script, '?')) {
            // Remove query vars
            $current_script = substr($current_script, 0, $pos);
        }
        $src2 = new Minify_Source(array(
            'id' => 'source1',
            'getContentFunc' => $getContentFunc,
            'contentType' => Minify::TYPE_CSS,
            'lastModified' => $last_modified,
            'minifyOptions' => array(
                // Get relative path from the current running script
                'currentDir' => str_repeat('.', substr_count(rtrim($current_script, '/'), '/')) . './',
            ),
        ));

        // Send headers
        header('Content-Type: text/css');
        header('Expires: ' . gmdate('D, d M Y H:i:s T', time() + $cache_limit));
        header('Cache-Control: public, max-age=' . $cache_limit);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', $last_modified));
        header('Pragma: public');

        // Do not use Minify::serve() because we don't want to use ETag
        echo Minify::combine('Files', array('files' => array(/*$src1,*/ $src2), 'bubbleCssImports' => true));

        exit;
    }
    
    public function getDefaultConfig()
    {
        return array(
            'front_page' => array(
                'path' => 'widgets',
                'show_title' => true,
            ),
            'display' => array(
                'breadcrumbs' => array(
                    'show' => true,
                    'show_full_path' => false,
                ),
            ),
            'debug' => array(
                'level' => Sabai_Log::NONE,
                'output' => 'html',
            ),
            'disableCssCache' => false,
            'mod_rewrite' => array(
                'enable' => false,
                'format' => rtrim($this->_application->SiteUrl(), '/') . '%1$s%3$s'
            ),
        );
    }
}
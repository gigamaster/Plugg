<?php
require_once 'Sabai/Event/Dispatcher.php';
require_once 'Sabai/Handle/Decorator/Cache.php';
require_once 'Sabai/Handle/Decorator/Autoload.php';
require_once 'Sabai/Handle/Class.php';

abstract class SabaiPlugin
{
    protected $_pluginDir;
    protected $_pluginPrefix;
    protected $_eventDispatcher;
    private $_pluginsInstalled;

    const PLUGIN_NAME_REGEX = '/^[a-zA-Z]+[a-zA-Z0-9]*[a-zA-Z0-9]+$/';

    protected function __construct($pluginDir, $pluginPrefix)
    {
        $this->_pluginDir = $pluginDir;
        $this->_pluginPrefix = $pluginPrefix;
        $this->_eventDispatcher = new Sabai_Event_Dispatcher();
    }

    public function getPluginDir()
    {
        return $this->_pluginDir;
    }

    /**
     * Gets an already installed plugin
     *
     * @param string $pluginName
     * @return mixed SabaiPlugin_Plugin if plugin found, false otherwise
     */
    public function getPlugin($pluginName)
    {
        if (!$handle = $this->getPluginHandle($pluginName)) return false;

        return $handle->instantiate();
    }

    /**
     * Gets a handle object of already installed plugin
     *
     * @param string $pluginName
     * @ret Sabai_Handle
     */
    public function getPluginHandle($pluginName)
    {
        // Is the plugin subscribed to any events?
        if ($this->_eventDispatcher->listenerExists($pluginName)) {
            // Retrieve from registered listenters
            return $this->_eventDispatcher->getListenerHandle($pluginName);
        } else {
            if ($installed = $this->isPluginInstalled($pluginName)) {
                // The plugin is installed but without any subscribed events, so load the plugin manually
                return $this->_getPluginHandle($pluginName, $installed);
            }
        }

        return false;
    }

    /**
     * Gets a plugin which is not yet installed
     *
     * @param string $name
     * @param string $name
     * @return SabaiPlugin_Plugin
     */
    public function fetchPlugin($name)
    {
        if (!$local_data = $this->getLocalPlugin($name)) {
            return false;
        }

        return $this->_getPluginHandle($name, array(), $local_data)->instantiate();
    }

    public function dispatch($eventType, array $eventArgs = array(), $pluginName = null, $force = false)
    {
        $this->_eventDispatcher->dispatch($eventType, $eventArgs, $pluginName, $force);
    }

    protected function _createPluginHandle($name, $version, array $extra = array())
    {
        $plugin_path = $this->_pluginDir . '/' . $name;
        return new Sabai_Handle_Decorator_Autoload(
            new Sabai_Handle_Class($this->_pluginPrefix . $name . '_Plugin', array($name, $plugin_path, $version, $extra)),
            $plugin_path . '/Plugin.php'
        );
    }

    protected function _getPluginHandle($pluginName, array $pluginData, array $localData = null)
    {
        if (!isset($localData)) {
            if (!$localData = $this->getLocalPlugin($pluginName)) {
                return false;
            }
        }
        $plugin_extra = !empty($pluginData['extra']) ? array_merge($pluginData['extra'], $localData['extra']) : $localData['extra'];
        $plugin_version = isset($pluginData['version']) ? $pluginData['version'] : $localData['version'];
        
        return $this->_createPluginHandle($pluginName, $plugin_version, $plugin_extra);
    }

    public function reloadPlugins()
    {
        $this->_pluginHandles = array();
        unset($this->_pluginsInstalled);
        foreach (array('plugins_loaded', 'plugins_last_loaded', 'plugin_interfaces', 'plugins_interfaces_local', 'plugins_local') as $cache_id) {
            $this->_removeCachedPluginData($cache_id);
        }
        $this->loadPlugins();
    }

    public function loadPlugins($force = false, $forceLocal = false)
    {
        if ($force || (false === $data = $this->_isPluginDataCached('plugins_loaded'))) {
            $data = array();
            $this->_eventDispatcher->clear();
            $local = $this->getLocalPlugins($forceLocal);
            $installed_plugins = $this->getInstalledPlugins($force);
            foreach (array_keys($installed_plugins) as $plugin_name) {
                if ($plugin_data = @$local[$plugin_name]) {
                    $plugin_extra = array_merge($installed_plugins[$plugin_name]['extra'], $plugin_data['extra']);
                    $data[$plugin_name] = array('version' => $installed_plugins[$plugin_name]['version'], 'extra' => $plugin_extra, 'events' => $plugin_data['events']);
                }
            }
            $this->_cachePluginData($data, 'plugins_loaded');
            $this->_cachePluginData(time(), 'plugins_last_loaded');
        }
        foreach (array_keys($data) as $plugin_name) {
            $plugin_data = $data[$plugin_name];
            $this->_eventDispatcher->addListener($plugin_data['events'], $this->_createPluginHandle($plugin_name, $plugin_data['version'], $plugin_data['extra']), $plugin_name);
        }
    }

    public function getPluginsLastLoaded()
    {
        return $this->_isPluginDataCached('plugins_last_loaded');
    }
    
    public function getInstalledPlugins($force = false)
    {
        if ($force || !isset($this->_pluginsInstalled)) {
            $this->_pluginsInstalled = $this->_doGetInstalledPlugins();
        }
        return $this->_pluginsInstalled;
    }

    public function getLocalPlugin($pluginName, $force = false)
    {
        $local_plugins = $this->getLocalPlugins($force);
        return isset($local_plugins[$pluginName]) ? $local_plugins[$pluginName] : false;
    }

    public function isPluginInstalled($pluginName, $force = false)
    {
        $plugins = $this->getInstalledPlugins($force);
        return isset($plugins[$pluginName]) ? $plugins[$pluginName] : false;
    }

    public function getInstalledPluginInterfaces($force = false, $forceLocal = false)
    {
        if ($force || (false === $data = $this->_isPluginDataCached('plugin_interfaces'))) {
            $local = $this->getLocalPlugins($forceLocal);
            $installed_plugins = $this->getInstalledPlugins($force);
            $data = array();
            foreach (array_keys($installed_plugins) as $plugin_name) {
                if ($plugin_data = @$local[$plugin_name]) {
                    foreach ($plugin_data['interfaces'] as $interface) {
                        $data[$interface][$plugin_name] = $plugin_data['title'];
                    }
                }
            }
            $this->_cachePluginData($data, 'plugin_interfaces');
        }

        return $data;
    }

    public function getInstalledPluginsByInterface($interface, $force = false, $forceLocal = false)
    {
        $interfaces = $this->getInstalledPluginInterfaces($force, $forceLocal);

        return isset($interfaces[$interface]) ? $interfaces[$interface] : array();
    }
    
    public function getLocalPluginInterfaces($force = false, $forceLocal = false)
    {
        if ($force || (false === $data = $this->_isPluginDataCached('plugin_interfaces_local'))) {
            $local = $this->getLocalPlugins($forceLocal);
            $installed_plugins = $this->getInstalledPlugins($force);
            $data = array();
            foreach ($local as $plugin_name => $plugin_data) {
                foreach ($plugin_data['interfaces'] as $interface) {
                    $data[$interface][$plugin_name] = $plugin_data['title'];
                }
            }
            $this->_cachePluginData($data, 'plugin_interfaces_local');
        }

        return $data;
    }

    public function getLocalPluginsByInterface($interface, $force = false, $forceLocal = false)
    {
        $interfaces = $this->getLocalPluginInterfaces($force, $forceLocal);
        
        if (!isset($interfaces[$interface])) return array();
        
        return array_intersect_key($this->getLocalPlugins(), $interfaces[$interface]);
    }

    public function getLocalPlugins($force = false)
    {
        if ($force || (false === $plugins = $this->_isPluginDataCached('plugins_local'))) {
            $plugins = array();
            if ($dh = opendir($this->_pluginDir)) {
                while (false !== $file = readdir($dh)) {
                    if (preg_match(self::PLUGIN_NAME_REGEX, $file) && empty($data[$file])) {
                        $plugin_dir = $this->_pluginDir . '/' . $file;
                        if (is_dir($plugin_dir)) {
                            $plugin_file_info = $plugin_dir . '/Plugin.info';
                            $plugin_file_main = $plugin_dir . '/Plugin.php';
                            if (file_exists($plugin_file_info)
                                && file_exists($plugin_file_main)
                                && ($plugin_info = $this->_getPluginInfo($file, $plugin_file_info))
                            ) {
                                require_once $plugin_file_main;
                                $plugin_class_main = $this->_pluginPrefix . $file . '_Plugin';
                                if (class_exists($plugin_class_main, false)) {
                                    $plugin_events = array_map(array(__CLASS__, '_mapPluginEvent'), array_filter(get_class_methods($plugin_class_main), array(__CLASS__, '_filterPluginEvent')));
                                    $plugins[$file] = array_merge(
                                        $plugin_info,
                                        array(
                                            'title' => $plugin_info['title'],
                                            'events' => $plugin_events,
                                            'interfaces' => class_implements($plugin_class_main, false)
                                        )
                                    );
                                }
                            }
                        }
                    }
                }
                closedir($dh);
                ksort($plugins);
                $this->_cachePluginData($plugins, 'plugins_local');
            }
        }

        return $plugins;
    }
    
    protected function _getPluginInfo($pluginName, $pluginInfoFile)
    {
        return ($plugin_info = parse_ini_file($pluginInfoFile)) ? $plugin_info : false;
    }

    protected static function _filterPluginEvent($method)
    {
        return strpos(strtolower($method), 'on') === 0;
    }

    protected static function _mapPluginEvent($method)
    {
        return strtolower(substr($method, 2));
    }

    abstract protected function _isPluginDataCached($id);
    abstract protected function _removeCachedPluginData($id);
    abstract protected function _cachePluginData($data, $id);
    abstract protected function _doGetInstalledPlugins();
}
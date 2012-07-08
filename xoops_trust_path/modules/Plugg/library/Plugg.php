<?php
require_once 'Sabai/Application/Web.php';

final class Plugg extends Sabai_Application_Web
{
    public static $localeDir, $cacheDir, $pluginDir;
    private static $_isRunning = false, $_instances = array(), $_primaryInstanceId;
    private $_siteName, $_siteEmail, $_siteUrl, $_locator, $_pluginManager, $_gettext, $_type, $_initialized = false,
        $_plugins = array(), $_user, $_currentPluginName = 'System';

    // System version constants
    const VERSION = '1.1.0a1', PHP_VERSION_MIN = '5.2.0', MYSQL_VERSION_RECOMMENDED = '4.0.0';

    // Request parameter constants
    const PARAM_AJAX = '__ajax', PARAM_TOKEN = '__t';

    // Plugg app type constants
    const XOOPS = 1, XOOPSCUBE_LEGACY = 2, IMPRESSCMS = 4, WORDPRESS = 8, MODULE = 31, STANDALONE = 32, ALL = 63;

    // Route type constants
    const ROUTE_NORMAL = 0, ROUTE_TAB = 1, ROUTE_MENU = 2, ROUTE_CALLBACK = 3,
        ROUTE_TITLE_NORMAL = 0, ROUTE_TITLE_TAB = 1, ROUTE_TITLE_TAB_DEFAULT = 2, ROUTE_TITLE_MENU = 3;

    public static function start($charset, $lang, $localeDir, $pluginDir, $cacheDir, $startSession = true)
    {
        Sabai::start(Sabai_Log::ERROR, $charset, $lang, $startSession);
        self::$localeDir = $localeDir;
        self::$pluginDir = $pluginDir;
        self::$cacheDir = $cacheDir;

        // Configure autoloading
        require_once 'Zend/Loader/Autoloader.php';
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->registerNamespace(array('Sabai_', 'SabaiPlugin_'));
        $loader->pushAutoloader(array('Plugg', 'autoload'), 'Plugg_');
    }

    public static function started()
    {
        return Sabai::started();
    }

    public static function autoload($class)
    {
        if (($partial = substr($class, 6))
            && strpos($partial, '_') // Depth is more than 1 level
        ) {
            // Plugin file
            require self::$pluginDir . '/' . str_replace('_', '/', $partial) . '.php';
        } else {
            // Plugg library file
            Zend_Loader::loadClass($class);
        }

        return true;
    }
    
    public static function create($id, $title, $url, array $config = array(), $type = self::STANDALONE, $routeParam = 'q')
    {
        if (!isset(self::$_primaryInstanceId)) self::$_primaryInstanceId = $id;
        
        $plugg = new self($id, $title, $url, $type, $routeParam);
        $plugg->_init($config);
        self::$_instances[$id] = $plugg;

        return $plugg;
    }

    public static function get($id)
    {   
        return self::$_instances[$id];
    }

    public static function exists($id = null)
    {
        if (!isset($id)) {
            if (!isset(self::$_primaryInstanceId)) return false;
            
            $id = self::$_primaryInstanceId;
        }
        
        return isset(self::$_instances[$id]) ? $id : false;
    }

    protected function __construct($id, $title, $url, $type, $routeParam)
    {
        parent::__construct($id, 'Plugg', $title, dirname(__FILE__) . '/Plugg', $routeParam, $url);

        $this->_type = $type;
        $this->_contentIdPrefix = 'plugg-';
        $this->_helperPrefix = __CLASS__ . '_Helper_';

        $this->addHelperDir($this->getPath() . '/Helper');
    }

    private function _init($config)
    {
        // Make sure the following configuration options are defined
        $config_keys = array('siteName', 'siteEmail', 'siteUrl', 'dbScheme', 'dbOptions', 'dbTablePrefix');
        if ($keys_undefined = array_diff($config_keys, array_keys($config))) {
            throw new Plugg_Exception(sprintf('Missing Plugg configuration options: %s', implode(', ', $keys_undefined)));
        }
        
        $this->_siteName = $config['siteName'];
        $this->_siteEmail = $config['siteEmail'];
        $this->_siteUrl = $config['siteUrl'];

        // Build service locator
        require_once 'Sabai/Service/Locator.php';
        $this->_locator = new Sabai_Service_Locator();
        $this->_locator->addProviderFactoryMethod(
            'DBConnection',
            array('Sabai_DB_Connection', 'factory'),
            array('scheme' => $config['dbScheme'], 'options' => $config['dbOptions']),
            'Sabai/DB/Connection.php'
        );
        $this->_locator->addProviderFactoryMethod(
            'DB',
            array('Sabai_DB', 'factory'),
            array(
                'DBConnection' => new stdClass,
                'tablePrefix' => $config['dbTablePrefix']
            ),
            'Sabai/DB.php'
        );
        $this->_locator->addProviderClass(
            'Model',
            array(
                'DB' => new stdClass,
                'modelDir' => dirname(__FILE__) . '/Model',
                'modelPrefix' => 'Plugg_Model_'
            ),
            'Sabai_Model',
            'Sabai/Model.php'
        );
        $this->_locator->addProviderClass(
            'PluginModel',
            array(
                'plugin' => null,
            ),
            'Plugg_PluginModel',
            'Plugg/PluginModel.php'
        );
        $this->_locator->addProviderClass(
            'Cache',
            array(
                'cacheName' => $this->getId(),
                'cacheDir' => self::$cacheDir . '/',
                'automaticSerialization' => false,
                'lifeTime' => null
            ),
            'Plugg_Cache',
            'Plugg/Cache.php'
        );

        // Load global message catalogue
        require_once 'Plugg/Gettext.php';
        $this->_gettext = new Plugg_Gettext($this);
        $this->_gettext->loadMessages($this->getId(), 'Plugg.mo');

        // Set plugin manager
        require_once 'Plugg/PluginManager.php';
        $this->_pluginManager = new Plugg_PluginManager($this);
    }

    public function initialize()
    {
        if ($this->_initialized) return $this;

        // Load plugins and invoke PluggInit event
        $this->_pluginManager->loadPlugins(false);
        $this->_pluginManager->dispatch('PluggInit');

        $this->_initialized = true;

        return $this;
    }

    public function run(Sabai_Application_Controller $controller, Plugg_Request $request)
    {
        if (self::$_isRunning) return;

        self::$_isRunning = true;
        
        self::$_primaryInstanceId = $this->_id;

        if ($ajax_target_id = $request->isAjax()) {
            $response = new Plugg_AjaxResponse($ajax_target_id);
            $response->setLayoutEnabled(false);

            // Remove the navigation part if the target region is not the whole plugg content
            if ($ajax_target_id != 'plugg-content') {
                $response->setNavigationEnabled(false);
            }
        } else {
            $response = new Plugg_Response();
        }
        
        // Invoke PluggRun event
        $this->_pluginManager->dispatch('PluggRun', array($controller, $request, $response));

        return parent::run($controller, $request, $response);
    }
    
    public static function isRunning()
    {
        return self::$_isRunning;
    }

    public function runCron($lastRun = null)
    {
        self::$_isRunning = true;
        
        $cache = $this->_locator->getService('Cache');

        // Get cached last run timestamp if not speficied
        if (!isset($lastRun)) $lastRun = $cache->get('cron_lastrun', $this->getId());
        
        $logs = array($this->_('Cron started.'));

        // Invoke plguins
        $this->_pluginManager->dispatch('PluggCron', array(intval($lastRun), &$logs));
        
        $cache->save(time(), 'cron_lastrun', $this->getId());
        
        $logs[] = $this->_('Cron stopped.');
        
        return $logs;
    }

    public function isType($type)
    {
        return ($this->_type & $type) == $type;
    }

    public function getType()
    {
        return $this->_type;
    }
    
    public function getSiteName()
    {
        return $this->_siteName;
    }
    
    public function getSiteEmail()
    {
        return $this->_siteEmail;
    }
    
    public function getSiteUrl()
    {
        return $this->_siteUrl;
    }
    
    public function getLocator()
    {
        return $this->_locator;
    }

    public function getGettext()
    {
        return $this->_gettext;
    }

    public function getPluginManager()
    {
        return $this->_pluginManager;
    }

    public function getUser()
    {   
        if (!isset($this->_user)) {
            if ($user_plugin = $this->getPlugin('User')) {
                $this->_user = $user_plugin->getCurrentUser();
            } else {
                $this->_user = new Sabai_User(new Sabai_User_AnonymousIdentity(), false);
            }
        }

        return $this->_user;
    }

    public function setUser(Sabai_User $user)
    {
        $this->_user = $user;
    }

    public function setCurrentPlugin(Plugg_Plugin $plugin)
    {
        $this->_currentPluginName = $plugin->name;
    } 

    
    /**
     * A shortcut method for fetching a plugin object
     * @param string $pluginName
     * @param bool $mustBeActive
     * @return Plugg_Plugin
     */
    public function getPlugin($pluginName = null, $mustBeActive = true)
    {
        if (empty($pluginName)) $pluginName = $this->_currentPluginName;

        if (!isset($this->_plugins[$pluginName])) {
            if (!$plugin = $this->_pluginManager->getPlugin($pluginName, $mustBeActive)) {
                return false;
            }
            
            $this->_plugins[$pluginName] = $plugin;
        }

        return $this->_plugins[$pluginName];
    }
    
    /**
     * A shortcut method for fetching the model object of a plugin
     * @param string $pluginName
     * @return Plugg_PluginModel
     */
    public function getPluginModel($pluginName = null)
    {
        return $this->getPlugin($pluginName)->getModel();
    }
    
    /**
     * A utility method to process a predefined callback with additional parameters.
     */
    public static function processCallback($callback, array $params = array())
    {
        if (is_array($callback) && is_array(@$callback[1])) {
            $params = empty($params) ? $callback[1] : array_merge($params, $callback[1]);
            $callback = $callback[0];
        }

        return call_user_func_array($callback, $params);
    }
    
    protected function _loadHelper($name)
    {
        if (parent::_loadHelper($name)) return true; // helper found
        
        if (!strpos($name, '_', 1)) return false; // global helper not found
        
        // Search plugin's helper directory
        if ((list($plugin_name, $file_name) = explode('_', $name))
            && ($plugin = $this->getPlugin($plugin_name))
        ) {
            require $plugin->path . '/helpers/' . $file_name . '.php';
            $class = $this->_helperPrefix . $name;
            $this->setHelper($name, new $class());
            
            return true;
        }
        
        return false;
    }
}
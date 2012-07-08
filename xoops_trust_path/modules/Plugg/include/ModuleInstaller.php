<?php
require_once 'SabaiXOOPS/ModuleInstaller.php';

class Plugg_Application_XOOPSCubeLegacy_ModuleInstaller extends SabaiXOOPS_ModuleInstaller
{
    private $_app;

    public function __construct(Sabai_Application $app)
    {
        parent::__construct();
        $this->_app = $app;
    }

    protected function _doExecute($module)
    {
        $log = new ArrayObject();
        
        // Init cache directories
        $log[] = 'Initializing cache directories...';
        foreach (array(Plugg::$cacheDir) as $dir) {
            if (!is_writable($dir) && !@chmod($dir, 0777)) {
                $log[] =sprintf('failed to make %s writable..please set the permission manually...', $dir);
            }
        }
        $log[] ='done...';

        // Install required plugins
        $log[] = 'Installing required plugins...';

        // Install the System plugin
        if (!$system = $this->_app->getPluginManager()->fetchPlugin('System')) {
            $log[] ='failed fetching the System plugin.';
            $this->addLog($log);
            return false;
        }
        $message = '';
        if (!$system->install($message) || (!$system_entity = $system->isPluginInstalled('System'))) {
            $log[] =sprintf('failed installing the System plugin. Error: %s', $message);
            $this->addLog($log);
            return false;
        }
        $this->_app->getPluginManager()->reloadPlugins();

        $log[] ='System installed...';

        // Install other required plugins
        $plugins_required = array(
            'Form' => array(),
            'Widgets' => array('priority' => 5),
            'AdminWidget' => array('priority' => 5),
            'HTMLPurifier' => array(),
            'Filters' => array('priority' => 7),
            'HTMLPurifierFilter' => array(),
            'Mail' => array('params' => array('mailerPlugin' => 'XOOPSCube')),
            'Uploads' => array(
                'params' => array(
                    'uploadDir' => XOOPS_TRUST_PATH . '/modules/Plugg/uploads',
                    'images' => array(
                        'thumbnailDir' => XOOPS_ROOT_PATH . '/modules/' . $module->getVar('dirname') . '/thumbnails',
                        'thumbnailUrl' => XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/thumbnails',
                        'thumbnailEnable' => true,
                        'thumbnailWidth' => 100,
                        'thumbnailHeight' => 100,
                        'maxImageWidth' => 800,
                        'maxImageHeight' => 600,
                    ),
                )
            ),
            'ImageTransform' => array(),
            'Content' => array(),
            'Search' => array('params' => array('searchEnginePlugin' => 'SimpleSearch'), 'priority' => 3),
            'XOOPSCube' => array('priority' => 99),
            'User' => array('params' => array('userManagerPlugin' => 'XOOPSCubeUserAPI'), 'priority' => 10),
            'Friends' => array(),
            'XOOPSCubeUserAPI' => array(),
            'Groups' => array('priority' => 8),
            'jQuery' => array(),
            'SimpleSearch' => array(),
            'XOOPSCodeFilter' => array(),
            'XOOPSCubeUser' => array(),
            'ExternalLink' => array(),
            'OpenIDAuth' => array(),
            'Messages' => array(),
            'Locale' => array(),
            'Footprints' => array(),
            'Cron' => array(),
            'Aggregator' => array(),
            'Forum' => array(),
        );
        $plugins_installed = array('System' => $system_entity);
        $install_failed = false;
        foreach ($plugins_required as $plugin_lib => $plugin_settings) {
            $plugin_settings = array_merge(array('params' => array(), 'priority' => 1), $plugin_settings);
            $error = '';
            if (!$plugin_data = $system->isPluginInstallable($plugin_lib, $error)) {
                $install_failed = true;
                $log[] =sprintf('failed installing required plugin %s. Error: %s', $plugin_lib, $error);
                break;
            } else {
                $result = $system->installPlugin(
                    $plugin_lib, $plugin_data, $plugin_settings['params'], $plugin_settings['priority']
                );
                if (!is_object($result)) {
                    $install_failed = true;
                    $log[] =sprintf('failed installing required plugin %s. Error: %s', $plugin_lib, $result);
                    break;
                } else {
                    $plugins_installed[$plugin_lib] = $result;
                    
                    // chmod directories or files if configured and display warning messages if chmod fails
                    if (!empty($plugin_data['chmod'])) {
                        foreach ((array)$plugin_data['chmod'] as $_file) {
                            if (!empty($plugin_settings['params'][$_file])) $_file = $plugin_settings['params'][$_file];
                            
                            if (false === strpos($_file, '/') && false === strpos($_file, DIRECTORY_SEPARATOR)) continue; // invalid path
                            
                            $path = Plugg::$pluginDir . '/' .  $plugin_lib . $_file;
                            
                            if (is_writable($path)) continue; // already writable
                               
                            if (!file_exists($path) || !@chmod($path, is_dir($_file) ? 0777 : 0666)) {
                                $log[] = sprintf('failed to make %s writeable..please chmod manually...', $path);
                            }
                        }
                    }
                    
                    $log[] = sprintf('%s installed...', $plugin_lib);
                }
            }
        }

        if (!$install_failed) {
            $log[] ='Creating default user role...';
            if ($install_failed = false === $this->_createDefaultUserRoles($module)) {
                $log[] ='failed.';
            }
        }

        if (!$install_failed) {
            $log[] ='Adding module access permissions...';
            if ($install_failed = false === $this->_addModuleAccessPermissions($module)) {
                $log[] ='failed.';
            }
        }

        // Uninstall all plugins if requierd plugins were not installed
        if ($install_failed) {
            if (!empty($plugins_installed)) {
                $log[] = 'Uninstalling installed plugins...';
                foreach (array_keys($plugins_installed) as $plugin_name) {
                    $message = '';
                    if ((!$plugin = $this->_app->getPlugin($plugin_name)) || !$plugin->uninstall($message)) {
                        $log[] =sprintf('failed uninstalling the %s plugin! You must manually uninstall the plugin. Error: %s..', $plugin_name, $message);
                        continue;
                    }
                    $log[] =sprintf('%s uninstalled...', $plugin_name);
                }
            }
        } else {
            foreach ($plugins_installed as $plugin_installed => $plugin_entity) {
                $this->_app->DispatchEvent($plugin_installed . 'Installed', array($plugin_entity));
                $this->_app->DispatchEvent('SystemAdminPluginInstalled', array($plugin_entity));
                
            }
        }
        $log[] ='done.';
        $this->addLog($log);
        
        // Reload plugins data
        $this->_app->getPluginManager()->reloadPlugins();

        return !$install_failed;
    }

    /**
     * Creates a default Plugg user role and assign it to the default XOOPS users group
     */
    private function _createDefaultUserRoles($module)
    {
        if (!$user_plugin = $this->_app->getPlugin('User')) return false; // this should not happen

        // Collect default permissions from installed plugins
        $permissions = array();
        $permissionables = $this->_app->getPluginManager()->getInstalledPluginsByInterface('Plugg_User_Permissionable');
        foreach (array_keys($permissionables) as $plugin_name) {
            if (!$permissionable = $this->_app->getPlugin($plugin_name)) continue;
            
            $permissions[$plugin_name] = $permissionable->userPermissionableGetDefaultPermissions();
        }
        $role = $user_plugin->getModel()->create('Role')->markNew();
        $role->name = 'default';
        $role->display_name = 'Default role';
        $role->system = 1;
        $role->setPermissions($permissions);
        if (!$role->commit()) return false;

        // Assign the role to the default users group
        $module_id = $module->getVar('mid');
        $perm_name = $module->getVar('dirname') . '_role';

        return xoops_gethandler('groupperm')->addRight($perm_name, $role->id, XOOPS_GROUP_USERS, $module_id);
    }

    /**
     * Ensure all groups have access to this module
     */
    private function _addModuleAccessPermissions($module)
    {
        $module_id = $module->getVar('mid');
        $gperm_handler = xoops_gethandler('groupperm');
        return $gperm_handler->addRight('module_read', $module_id, XOOPS_GROUP_ANONYMOUS)
            && $gperm_handler->addRight('module_read', $module_id, XOOPS_GROUP_USERS);
    }
}
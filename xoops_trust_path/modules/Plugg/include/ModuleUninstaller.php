<?php
require_once 'SabaiXOOPS/ModuleInstaller.php';

class Plugg_Application_XOOPSCubeLegacy_ModuleUninstaller extends SabaiXOOPS_ModuleInstaller
{
    private $_app;
    private $_lastVersion;

    public function __construct(Sabai_Application $app, $lastVersion)
    {
        parent::__construct('Legacy.Admin.Event.ModuleUninstall.%s.Success', 'Legacy.Admin.Event.ModuleUninstall.%s.Fail');
        $this->_app = $app;
        $this->_lastVersion = $lastVersion;
    }

    protected function _doExecute($module)
    {
        // Uninstall the plugins
        if ($plugins_installed = @$this->_app->getPluginManager()->getInstalledPlugins(true)) {
            $log = 'Uninstalling installed plugins...';
            foreach (array_keys($plugins_installed) as $plugin_name) {
                 if ($plugin = $this->_app->getPlugin($plugin_name, false)) {
                     $message = '';
                     if (!$plugin->uninstall($message)) {
                         $log .= sprintf('failed uninstalling the %s plugin. You must manually uninstall the plugin. Error: %s...', $plugin_name, $message);
                         continue;
                     } else {
                         $log .= '...';
                     }
                 }
                 $log .= sprintf('%s uninstalled...', $plugin_name);
            }
            $log .= 'done.';
            $this->addLog($log);
        }

        $log = 'Removing cache files...';
        if (!$this->_app->getLocator()->getService('Cache')->clean()) {
            $log .= 'failed...';
        } else {
            $log .= '...';
        }
        $log .= 'done.';
        $this->addLog($log);

        return true;
    }
}
<?php
require_once 'SabaiPlugin.php';

class Plugg_PluginManager extends SabaiPlugin
{
    protected $_plugg;
    private $_repository, $_cache;

    public function __construct(Plugg $plugg)
    {
        parent::__construct(Plugg::$pluginDir, 'Plugg_');
        $this->_repository = $plugg->getLocator()->createService(
            'Model',
            array(
                'DB' => $plugg->getLocator()->createService('DB', array(
                    'tablePrefix' => $plugg->getLocator()->getDefaultParam('DB', 'tablePrefix') . 'system_'
                )),
                'modelDir' => $this->_pluginDir . '/System/Model',
                'modelPrefix' => $this->_pluginPrefix . 'System_Model_'
            )
        )->getRepository('Plugin');
        $this->_cache = $plugg->getLocator()->getService('Cache');
        $this->_plugg = $plugg;
    }

    protected function _isPluginDataCached($id)
    {
        if (!$cached = $this->_cache->get($id)) {
            return false;
        }
        return unserialize($cached);
    }

    protected function _cachePluginData($data, $id)
    {
        return $this->_cache->save(serialize($data), $id);
    }
    
    protected function _removeCachedPluginData($id)
    {
        return $this->_cache->remove($id);
    }

    protected function _doGetInstalledPlugins()
    {
        $ret = array();
        foreach ($this->_repository->fetch() as $plugin) {
            $ret[$plugin->name] = array(
                'version' => $plugin->version,
                'extra' => array('nicename' => $plugin->nicename, 'params' => $plugin->getParams(false)),
            );
        }
        return $ret;
    }

    /**
     * Overrides the parent method to pass in the Plugg instance
     */
    protected function _getPluginInfo($pluginName, $pluginInfoFile)
    {
        if (!$plugin_info = parent::_getPluginInfo($pluginName, $pluginInfoFile)) {
            return false;
        }
        $plugin_dir = dirname($pluginInfoFile);
        
        return array_merge(
            $plugin_info,
            array(
                'extra' => array(
                    'hasModel' => is_dir($plugin_dir . '/Model'),
                    'hasSchema' => file_exists($plugin_dir . '/schema/latest.xml'),
                    'hasMainCSS' => file_exists($plugin_dir . '/css/main.css'),
                    'hasAdminCSS' => file_exists($plugin_dir . '/css/admin.css'),
                    'hasLocale' => file_exists($plugin_dir . '/' . $pluginName . '.pot'),
                )
            )
        );
    }

    /**
     * Overrides the parent method to pass in the Plugg instance
     */
    protected function _createPluginHandle($name, $version, array $extra = array())
    {
        $plugin_path = $this->_pluginDir . '/' . $name;
        $handle = new Sabai_Handle_Decorator_Cache(
            new Sabai_Handle_Decorator_Autoload(
                new Sabai_Handle_Class($this->_pluginPrefix . $name . '_Plugin',
                    array($name, $plugin_path, $version, $extra, $this->_plugg)),
                $plugin_path . '/Plugin.php'
            )
        );
        return $handle;
    }
}
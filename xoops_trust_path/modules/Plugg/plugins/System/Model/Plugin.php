<?php
class Plugg_System_Model_Plugin extends Plugg_System_Model_Base_Plugin
{
    public function getParams($includeNonCacheable = true)
    {
        if (!$params = @unserialize($this->params)) return array();

        if (!isset($params[0])) return $params; // backward compat

        if (!$includeNonCacheable) return $params[0];

        return array_merge($params[0], $params[1]);
    }

    public function setParams(array $params, array $paramsNonCacheable = array(), $merge = true)
    {
        if ($merge) {
            $all_params = array_merge($this->getParams(), $params, $paramsNonCacheable);
            $params = array_diff_key($all_params, $paramsNonCacheable);
            $paramsNonCacheable = array_diff_key($all_params, $params);
        }

        $this->params = serialize(array($params, $paramsNonCacheable));
        
        return $this;
    }

    public function isUninstallable()
    {
        return (bool)$this->uninstallable;
    }
    
    public function isRequiredByOtherPlugins()
    {
        $plugins = array();
        $deps = $this->_model->Dependency->criteria()->requires_is($this->id)->fetch()->with('Plugin');
        foreach ($deps as $dep) {
            $plugins[] = $dep->Plugin;
        }
        
        return $plugins;
    }
}

class Plugg_System_Model_PluginRepository extends Plugg_System_Model_Base_PluginRepository
{
    public function fetchByName($name)
    {
        $criteria = Sabai_Model_Criteria::createValue('plugin_name', $name);
        return $this->fetchByCriteria($criteria, 1, 0)->getFirst();
    }
}
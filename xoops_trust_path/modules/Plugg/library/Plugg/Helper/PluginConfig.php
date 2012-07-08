<?php
class Plugg_Helper_PluginConfig extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $configName, $pluginName = null)
    {   
        return call_user_func_array(array($application->getPlugin($pluginName), 'getConfig'), (array)$configName);
    }
}
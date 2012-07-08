<?php
class Plugg_Helper_PluginName extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $nicename = true, $pluginName = null)
    {
        $property = $nicename ? 'nicename' : 'name';
        
        return $application->getPlugin($pluginName)->$property;
    }
}
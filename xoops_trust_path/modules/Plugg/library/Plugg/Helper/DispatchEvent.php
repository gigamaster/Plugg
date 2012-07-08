<?php
class Plugg_Helper_DispatchEvent extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $eventName, array $eventParams = array(), $pluginName = null, $force = false)
    {
        return $application->getPluginManager()->dispatch($eventName, $eventParams, $pluginName, $force);
    }
}
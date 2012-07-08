<?php
class Plugg_Helper__ extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $msgId, $pluginName = null)
    {
        return ($plugin = $application->getPlugin($pluginName))
            ? $plugin->_($msgId)
            : $application->getGettext()->gettext($msgId);
    }
}
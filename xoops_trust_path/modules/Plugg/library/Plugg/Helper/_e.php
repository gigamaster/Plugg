<?php
class Plugg_Helper__e extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $msgId, $pluginName = null)
    {
        echo ($plugin = $application->getPlugin($pluginName))
            ? $plugin->_($msgId)
            : $application->getGettext()->gettext($msgId);
    }
}
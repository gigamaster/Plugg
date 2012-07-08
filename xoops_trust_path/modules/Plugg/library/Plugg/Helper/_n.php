<?php
class Plugg_Helper__n extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $msgId, $msgId2, $num, $pluginName = null)
    {
        return ($plugin = $application->getPlugin($pluginName))
            ? $plugin->_n($msgId, $msgId2, $num)
            : $application->getGettext()->ngettext($msgId, $msgId2, $num);
    }
}
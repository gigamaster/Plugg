<?php
class Plugg_Helper_DateTime extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $time, $short = false)
    {
        return formatTimestamp($time, $short ? 's' : 'm');
    }
}
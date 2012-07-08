<?php
class Plugg_Helper_JsUrl extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $plugin, $file, $dir = '', $separator = '&amp;')
    {
        return $application->createUrl(array(
            'base' => '/',
            'script' => 'js',
            'params' => array(
                'plugin' => $plugin,
                'file' => $file,
                'dir' => $dir
            ),
            'separator' => $separator
        ));
    }
}
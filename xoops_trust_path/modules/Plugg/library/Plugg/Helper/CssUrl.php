<?php
class Plugg_Helper_CssUrl extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $plugin, $file = '', $dir = '', $separator = '&amp;')
    {
        return $application->createUrl(array(
            'base' => '/',
            'script' => 'css',
            'params' => array(
                'plugin' => $plugin,
                'file' => $file,
                'dir' => $dir
            ),
            'separator' => $separator
        ));
    }
}
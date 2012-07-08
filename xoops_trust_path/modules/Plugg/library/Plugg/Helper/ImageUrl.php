<?php
class Plugg_Helper_ImageUrl extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $plugin, $file, $dir = '', $separator = '&amp;')
    {
        return $application->createUrl(array(
            'base' => '/',
            'script' => 'image',
            'params' => array(
                'plugin' => $plugin,
                'file' => $file,
                'dir' => $dir
            ),
            'separator' => $separator
        ));
    }
}
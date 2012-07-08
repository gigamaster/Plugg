<?php
class Plugg_Helper_Uploads_ThumbnailUrl extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $file = null)
    {
        if (!isset($file)) $application->getPlugin('Uploads')->getConfig('images', 'thumbnailUrl');
        
        return $application->getPlugin('Uploads')->getConfig('images', 'thumbnailUrl') . '/' . $file;
    }
}
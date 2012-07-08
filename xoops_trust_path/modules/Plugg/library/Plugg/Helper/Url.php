<?php
class Plugg_Helper_Url extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $path = '', array $params = array(), $fragment = '', $separator = '&amp;')
    {
        return is_array($path) ? $application->createUrl($path) : $application->getUrl($path, $params, $fragment, $separator);
    }
}
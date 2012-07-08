<?php
class Plugg_Helper_User_IdentityUrl extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, Sabai_User_AbstractIdentity $user, $path = '', array $params = array(), $separator = '&amp;')
    {
        if ($user->isAnonymous()) return $application->getSiteUrl();
        
        return $application->createUrl(array(
            'base' => '/user/profile/' . $user->username . '/',
            'script' => 'main',
            'path' => $path,
            'params' => $params,
            'separator' => $separator
        ));
    }
}
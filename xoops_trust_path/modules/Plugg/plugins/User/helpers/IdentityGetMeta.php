<?php
class Plugg_Helper_User_IdentityGetMeta extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, Sabai_User_AbstractIdentity $identity, $metaKey)
    {
        if (!is_object($identity)) {
            $identity = $application->User_Identity($identity, true);
        }
        
        if ($identity->isAnonymous()) return;
        
        return is_array($metaKey)
            ? call_user_func_array(array($identity, 'getData'), $metaKey)
            : $identity->getData($metaKey);
    }
}
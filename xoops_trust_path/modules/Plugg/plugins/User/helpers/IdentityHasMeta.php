<?php
class Plugg_Helper_User_IdentityHasMeta extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, Sabai_User_AbstractIdentity $identity, $metaKey)
    {
        if (!is_object($identity)) {
            $identity = $application->User_Identity($identity, true);
        }
        
        if ($identity->isAnonymous()) return false;
        
        return is_array($metaKey)
            ? call_user_func_array(array($identity, 'hasData'), $metaKey)
            : $identity->hasData($metaKey);
    }
}
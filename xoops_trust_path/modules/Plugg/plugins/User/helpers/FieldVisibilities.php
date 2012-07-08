<?php
class Plugg_Helper_User_FieldVisibilities extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, $allFriendsAsOne = false)
    {
        $user_plugin = $application->getPlugin('User');
        
        // Initialize visibility element
        $visibilities = array(
            '@private' => $user_plugin->_('nobody'),
            '@all' => $user_plugin->_('everybody'),
            '@user' => $user_plugin->_('registered users')
        );
        if ($friends_plugin = $application->getPlugin('Friends')) {
            if ($allFriendsAsOne) {
                $visibilities['@friends'] = $user_plugin->_('friends');
            } else {
                foreach ($friends_plugin->getXFNMetaDataList(false) as $k) {
                    $visibilities[$k] = sprintf($user_plugin->_('friends with "%s" relationship'), $k);
                }
            }
        }
        
        return $visibilities;
    }
}
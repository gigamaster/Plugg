<?php
class Plugg_Helper_Groups_GroupDefaultAvatarUrl extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, Plugg_Groups_Model_Group $group)
    {
        return $application->ImageUrl('Groups', 'no_avatar.gif');
    }
}
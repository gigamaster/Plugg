<?php
class Plugg_Helper_Groups_GroupDefaultThumbnailUrl extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, Plugg_Groups_Model_Group $group)
    {
        return $application->ImageUrl('Groups', 'no_avatar_thumbnail.gif');
    }
}
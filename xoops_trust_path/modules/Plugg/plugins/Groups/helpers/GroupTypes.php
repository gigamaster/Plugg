<?php
class Plugg_Helper_Groups_GroupTypes extends Sabai_Application_Helper
{
    /**
     * Creates a small image HTML link to the top page of a group
     *
     * @return string
     * @param Sabai_Application $application
     */
    public function help(Sabai_Application $application)
    {
        return $application->getPlugin('Groups')->getGroupTypes();
    }
}
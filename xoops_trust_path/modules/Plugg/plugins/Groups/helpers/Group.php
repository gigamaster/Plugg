<?php
class Plugg_Helper_Groups_Group extends Sabai_Application_Helper
{
    /**
     * Creates a small image HTML link to the top page of a group
     *
     * @return Plugg_Groups_Model_Group
     * @param Sabai_Application $application
     * @param int $groupId
     */
    public function help(Sabai_Application $application, $groupId)
    {
        return $application->getPlugin('Groups')->getModel()->Group->fetchById($groupId);
    }
}
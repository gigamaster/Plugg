<?php
class Plugg_Helper_Groups_Groups extends Sabai_Application_Helper
{
    /**
     * Creates a small image HTML link to the top page of a group
     *
     * @return Sabai_Model_EntityCollection
     * @param Sabai_Application $application
     * @param array $groupIds
     */
    public function help(Sabai_Application $application, array $groupIds)
    {
        return $application->getPlugin('Groups')->getModel()->Group->fetchByIds($groupIds);
    }
}
<?php
class Plugg_Helper_User_Roles extends Sabai_Application_Helper
{
    /**
     * Creates an image HTML link to user profile page
     *
     * @return string
     * @param SabaiApplication $application
     * @param Sabai_User_Identity $identity
     * @param string $rel
     */
    public function help(Sabai_Application $application, $sort = 'name', $order = 'ASC')
    {
        return $application->getPlugin('User')->getModel()->Role->fetch(0, 0, $sort, $order);
    }
}
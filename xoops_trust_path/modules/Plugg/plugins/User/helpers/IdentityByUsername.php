<?php
class Plugg_Helper_User_IdentityByUsername extends Sabai_Application_Helper
{
    /**
     * Creates an image HTML link to user profile page
     *
     * @return string
     * @param SabaiApplication $application
     * @param Sabai_User_Identity $identity
     * @param string $rel
     */
    public function help(Sabai_Application $application, $username, $withData = false)
    {
        return $application->getPlugin('User')->getIdentityFetcher()
            ->fetchUserIdentityByUsername($username, $withData);
    }
}
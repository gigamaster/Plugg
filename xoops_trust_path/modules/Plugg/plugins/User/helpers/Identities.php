<?php
class Plugg_Helper_User_Identities extends Sabai_Application_Helper
{
    /**
     * Creates an image HTML link to user profile page
     *
     * @return string
     * @param SabaiApplication $application
     * @param Sabai_User_Identity $identity
     * @param string $rel
     */
    public function help(Sabai_Application $application, array $userIds, $withData = false)
    {
        return $application->getPlugin('User')->getIdentityFetcher()
            ->fetchUserIdentities($userIds, $withData);
    }
}
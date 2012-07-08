<?php
class Plugg_User_Filter extends Sabai_Application_ControllerFilter
{
    public function before(Sabai_Request $request, Sabai_Application_Response $response, Sabai_Application $application)
    {   
        if (!$application->getUser()->isAuthenticated()) return;

        // Set identity data loader for lazy loading extra user data
        $identity_fetcher = $application->getPlugin('User')->getIdentityFetcher();
        $application->getUser()->getIdentity()->setDataLoader(array($identity_fetcher, 'loadIdentityWithData'));

        // Add CSS to display the link text 'You' for links to own profile page
        $response->addCss(sprintf(
            '#plugg a.plugg-user-%1$d span.plugg-user, .plugg a.plugg-user-%1$d span.plugg-user {display:none;}
             #plugg a.plugg-user-%1$d span.plugg-user-self, .plugg a.plugg-user-%1$d span.plugg-user-self {display:inline !important;}', // !important overrides element style settings
            $application->getUser()->id
        ));

        if ($application->getUser()->isFinalized() || $application->getUser()->isSuperUser()) return;

        // Set permissions for the user
        $members = $application->getPlugin('User')
            ->getModel()
            ->Member
            ->fetchByUser($application->getUser()->id)
            ->with('Role');
        foreach ($members as $member) {
            foreach ($member->Role->getPermissions() as $plugin_name => $permissions) {
                if (empty($permissions)) continue;
                foreach ($permissions as $permission) {
                    $application->getUser()->addPermission($permission);
                }
            }
        }

        $application->getUser()->finalize(true);
    }

    public function after(Sabai_Request $request, Sabai_Application_Response $response, Sabai_Application $application)
    {
    }
}
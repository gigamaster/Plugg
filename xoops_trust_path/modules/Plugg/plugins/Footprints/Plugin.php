<?php
class Plugg_Footprints_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_System_Routable_Admin, Plugg_User_Permissionable, Plugg_User_AccountSetting
{
    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/user/:user_id/footprints' => array(
                'controller' => 'User_Index',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_nicename,
            ),
            '/user/:user_id/footprints/manage' => array(
                'controller' => 'User_Manage',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('My footprints'),
                'title_callback' => true,
                'access_callback' => true,
            ),
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/user/:user_id/footprints/manage':
                return $this->_application->identity_is_me || $this->_application->getUser()->isSuperUser();
        }
    }

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/user/:user_id/footprints/manage':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('List') : $title;
        }
    }

    /* End implementation of Plugg_System_Routable_Main */

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/content/footprints' => array(
                'controller' => 'Index',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path){}

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType){}

    /* End implementation of Plugg_System_Routable_Admin */
    
    /* Start implementation of Plugg_User_Permissionable */

    public function userPermissionableGetPermissions()
    {
        return array(
            //'Footprint' => array(
                'footprints hide own' => $this->_('Hide own footprints'),
                'footprints disable own' => $this->_('Disable own footprints'),
            //),
        );
    }
    
    public function userPermissionableGetDefaultPermissions()
    {
        return array();
    }
    
    /* End implementation of Plugg_User_Permissionable */
    
    /* Start implementation of Plugg_User_AccountSetting */
    
    public function userAccountSettingGetNames()
    {
        return array('disable_my_footprint');
    }
    
    public function userAccountSettingGetSettings($settingName, Plugg_User_Identity $identity)
    {
        switch ($settingName) {
            case 'disable_my_footprint':
                return array(
                    '#type' => 'checkbox',
                    '#title' => $this->_('Do not leave my footprints.'),
                    '#description' => $this->_("Check this option to prevent your footprint, i.e. visit record, from being recorded whenever you visit other user's profile."),
                    '#disabled' => !$this->_application->User_IdentityPermissions($identity, 'footprints disable own'),
                );
        }
    }

    /* End implementation of Plugg_User_AccountSetting */

    public function onUserIdentityViewed($request, $identity)
    {
        // Never count guest users
        if (!$this->_application->getUser()->isAuthenticated()) return;

        $target = $identity->id;
        $viewer = $this->_application->getUser()->id;

        // Never add footprint when viewing own profile
        if ($target == $viewer) return;

        // Is footprint disabled by the user?
        if ($this->_application->getUser()->hasPermission('footprints disable own')
            && $this->_application->getUser()->getIdentity()->hasData('disable_my_footprint')
            && $this->_application->getUser()->getIdentity()->getData('disable_my_footprint')
        ) return;

        // Any footprint already during the session?
        if ($footprints = $this->getSessionVar('footprints')) {
            if (isset($footprints[$target])) return;
        } else {
            $footprints = array();
        }   
        $footprints[$target] = true;
        $this->setSessionVar('footprints', $footprints);

        $model = $this->getModel();
        if (!$footprint = $model->Footprint->criteria()->target_is($target)->fetchByUser($viewer)->getFirst()) {
            $footprint = $model->create('Footprint');
            $footprint->target = $target;
            $footprint->assignUser($this->_application->getUser());
            $footprint->markNew();
        }
        $footprint->timestamp = time();
        $footprint->commit();
    }

    public function onPluggCron($lastrun, array $logs)
    {
        // Allow run this cron 1 time per day at most
        if (!empty($lastrun) && time() - $lastrun < 86400) return;

        if (!$cron_days = intval($this->getConfig('cronIntervalDays'))) return;
        
        $logs[] = sprintf($this->_('Deleting footprint data older than %d days.'), $cron_days);

        // Delete footprints older than specified number of days
        $model = $this->getModel();
        $criteria = $model->createCriteria('Footprint')
            ->timestamp_isSmallerThan(time() - ($cron_days * 86400));
        $model->getGateway('Footprint')->deleteByCriteria($criteria);
    }

    public function onUserIdentityDeleteSuccess($identity)
    {
        $id = $identity->id;
        $model = $this->getModel();

        // Remove footprints for the user if any
        $criteria = $model->createCriteria('Footprint')->userId_is($id)->or_()->target_is($id);
        $model->getGateway('Footprint')->deleteByCriteria($criteria);
    }
    
    public function getDefaultConfig()
    {
        return array('cronIntervalDays' => 30);
    }
}
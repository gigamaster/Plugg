<?php
class Plugg_Friends_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_User_Permissionable, Plugg_User_Widget, Plugg_User_Menu
{
    const REQUEST_STATUS_PENDING = 0;
    const REQUEST_STATUS_REJECTED = 1;

    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/user/:user_id/friends' => array(
                'controller' => 'User_Index',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
            ),
            '/user/:user_id/friends_request' => array(
                'forward' => 'friends/request',
                'title' => $this->_('Add as friend'),
                'type' => Plugg::ROUTE_MENU,
                'access_callback' => true,
            ),
            '/user/:user_id/friends/request' => array(
                'controller' => 'User_SendRequest',
                'title' => $this->_('Send friend request'),
                'type' => Plugg::ROUTE_MENU,
                'access_callback' => true,
            ),
            '/user/:user_id/friends/manage' => array(
                'controller' => 'User_Manage',
                'title' => $this->_('Manage friends'),
                'type' => Plugg::ROUTE_MENU,
                'access_callback' => true,
            ),
            '/user/:user_id/friends/requests' => array(
                'controller' => 'User_Requests',
                'title' => $this->_('Requests'),
                'type' => Plugg::ROUTE_TAB,
                'access_callback' => true,
            ),
            '/user/:user_id/friends/requests/sent' => array(
                'controller' => 'User_Requests_Sent',
            ),
            '/user/:user_id/friends/requests/received' => array(
                'controller' => 'User_Requests_Received',
            ),
            '/user/:user_id/friends/manage/:friend_id' => array(
                'access_callback' => true,
                'format' => array(':friend_id' => '\d+'),
            ),
            '/user/:user_id/friends/manage/:friend_id/edit' => array(
                'controller' => 'User_Manage_EditFriend',
                'title' => $this->_('Edit friend relationships'),
            ),
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/user/:user_id/friends/requests':
            case '/user/:user_id/friends/manage':
                return $this->_application->identity_is_me || $this->_application->getUser()->hasPermission('friends manage any');
                    
            case '/user/:user_id/friends/manage/:friend_id':
                // Make sure the requested friend relationship exists and belongs to the user
                return ($id = $request->asInt('friend_id'))
                    && ($this->_application->friend = $this->getModel()->Friend->fetchById($id))
                    && ($this->_application->getUser()->hasPermission('friends manage any')
                        || $this->_application->friend->isOwnedBy($this->_application->identity));

            case '/user/:user_id/friends_request':
            case '/user/:user_id/friends/request':
                // Do not allow sending requests to self
                return $this->_application->getUser()->isAuthenticated()
                    && !$this->_application->identity_is_me;
        }
    }

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
    }

    /* End implementation of Plugg_System_Routable_Main */

    /* Start implementation of Plugg_User_Permissionable */

    public function userPermissionableGetPermissions()
    {
        return array(
            'friends manage any' => $this->_("Manage other user's friends data"),
        );
    }
    
    public function userPermissionableGetDefaultPermissions()
    {
        return array();
    }
    
    /* End implementation of Plugg_User_Permissionable */

    public function onUserIdentityDeleteSuccess($identity)
    {
        $id = $identity->id;
        $model = $this->getModel();
        $model->getGateway('Request')->deleteByCriteria($model->createCriteria('Request')->userId_is($id)->or_()->to_is($id));
        $model->getGateway('Friend')->deleteByCriteria($model->createCriteria('Friend')->userId_is($id)->or_()->with_is($id));
    }

    public function onPluggCron($lastrun, array $logs)
    {
        // Allow run this cron 1 time per day at most
        if (!empty($lastrun) && time() - $lastrun < 86400) return;
        
        $logs[] = $this->_('Deleting accepted/rejected friend requests older than 100 days.');

        $model = $this->getModel();

        // Remove unconfirmed but rejected/accepted friend requests that are more than 100 days old
        $criteria = $model->createCriteria('Request')
            ->status_isNot(self::REQUEST_STATUS_PENDING)
            ->created_isSmallerThan(time() - 8640000);
        $model->getGateway('Request')->deleteByCriteria($criteria);
    }

    /* Start implementation of Plugg_User_Widget */

    public function userWidgetGetNames()
    {
        return array(
            'friends' => Plugg_User_Plugin::WIDGET_TYPE_PUBLIC | Plugg_User_Plugin::WIDGET_TYPE_CACHEABLE
        );
    }

    public function userWidgetGetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'friends':
                return $this->_('Friends');
        }
    }

    public function userWidgetGetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'friends':
                return $this->_('Displays friends of the user.');
        }
    }

    public function userWidgetGetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'friends':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of friends to display'),
                        '#setting' => array(
                            'default'  => 10,
                            'options'  => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10, 15 => 15, 20 => 20),
                            'delimiter' => '&nbsp;'
                        ),
                        '#default_value' => @$currentValues['limit'],
                    ),
                );
        }
    }

    public function userWidgetGetContent($widgetName, $widgetSettings, Sabai_User_Identity $identity, $isOwner, $isAdmin)
    {
        switch ($widgetName) {
            case 'friends':
                return $this->_renderFriendsUserWidget($widgetSettings, $identity);
        }
    }

    private function _renderFriendsUserWidget($widgetSettings, $identity)
    {
        $id = $identity->id;
        $count = $this->getModel()->Friend->countByUser($id);
        if ($count <= 0) return;

        $friends = $this->getModel()->Friend
            ->fetchByUser($id, $widgetSettings['limit'], 0, 'created', 'DESC');

        $data['links'] = array(
            array(
                'url' => array('path' => 'friends'),
                'title' => sprintf($this->_('Show all (%d)'), $count),
            )
        );

        foreach ($friends->with('WithUser') as $friend) {
            $friend_identity = $friend->WithUser;
            if ($friend_identity->isAnonymous()) continue;
            $data['content'][] = array(
                'title' => $friend_identity->display_name,
                'body' => sprintf($this->_('Relationship: %s'), $friend->relationships),
                'url' => $this->_application->User_IdentityUrl($friend_identity),
                'thumbnail' => $friend_identity->image_thumbnail,
                'thumbnail_link' => $this->_application->User_IdentityUrl($friend_identity),
                'thumbnail_title' => $friend_identity->display_name,
                'timestamp' => $friend->created,
            );
        }

        return $data;
    }

    /* End implementation of Plugg_User_Widget */

    /* Start implementation of Plugg_User_Menu */

    public function userMenuGetNames()
    {
        return array('requests');
    }

    public function userMenuGetNicename($menuName)
    {
        switch ($menuName) {
            case 'requests':
                return $this->_('New friend requests');
        }
    }

    public function userMenuGetLinkText($menuName, Sabai_User $user)
    {
        switch ($menuName) {
            case 'requests':
                $count = $this->getModel()->Request
                    ->criteria()
                    ->to_is($user->id)
                    ->status_is(self::REQUEST_STATUS_PENDING)
                    ->count();
                if ($count == 0) return;

                return sprintf($this->_('Friend requests (<strong>%d</strong>)'), $count);
        }
    }

    public function userMenuGetLinkUrl($menuName, Sabai_User $user)
    {
        switch ($menuName) {
            case 'requests':
                return $this->_application->User_IdentityUrl($user, $this->_name . '/manage/requests/received');
        }
    }

    /* End implementation of Plugg_User_Menu */


    public function getXFNMetaDataList($categorize = true)
    {
        $list = array(
            'Friendship' => array(
                $this->_('contact'),
                $this->_('acquaintance'),
                $this->_('friend')
            ),
            'Physical' => array($this->_('met')),
            'Professional' => array(
                $this->_('co-worker'),
                $this->_('colleague')
            ),
            'Geographical' => array(
                $this->_('co-resident'),
                $this->_('neighbor')
            ),
            'Family' => array(
                $this->_('child'),
                $this->_('parent'),
                $this->_('sibling'),
                $this->_('spouse'),
                $this->_('kin')
            ),
            'Romantic' => array(
                $this->_('muse'),
                $this->_('crush'),
                $this->_('date'),
                $this->_('sweetheart')
            ),
            'Identity' => array($this->_('me'))
        );
        if ($categorize) return $list;

        $ret = array();
        foreach ($list as $k => $v) {
            $ret = array_merge($ret, $v);
        }

        return $ret;
    }

    public function getRelationships($from, $to)
    {
        $friend = $this->getModel()->Friend
            ->criteria()
            ->with_is(is_object($to) ? $to->id : $to)
            ->fetchByUser(is_object($from) ? $from->id : $from, 1, 0)
            ->getFirst();
        return $friend ? $friend->getRelationships() : array();
    }
}
<?php
class Plugg_Groups_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_System_Routable_Admin, Plugg_User_Permissionable, Plugg_User_Widget, Plugg_Groups_Widget, Plugg_Widgets_Widget, Plugg_AdminWidget_Widget
{
    const GROUP_STATUS_PENDING = 0, GROUP_STATUS_APPROVED = 1,
        GROUP_TYPE_NONE = 0, GROUP_TYPE_INVITATION_REQUIRED = 1, GROUP_TYPE_APPROVAL_REQUIRED = 2,
        MEMBER_STATUS_PENDING = 0, MEMBER_STATUS_INVITED = 1, MEMBER_STATUS_ACTIVE = 3,
        MEMBER_ROLE_NONE = 0, MEMBER_ROLE_ADMINISTRATOR = 1,
        WIDGET_POSITION_TOP = 0, WIDGET_POSITION_LEFT = 1, WIDGET_POSITION_RIGHT = 2, WIDGET_POSITION_BOTTOM = 3,
        WIDGET_TYPE_CACHEABLE = 1;

    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/groups' => array(
                'controller' => 'Index',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
            ),
            '/groups/list' => array(
                'controller' => 'List',
                'title' => $this->_('List view'),
                'type' => Plugg::ROUTE_MENU,
                'title_callback' => true,
            ),
            '/groups/:group_name' => array(
                'controller' => 'Group',
                'format' => array(':group_name' => '.+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/groups/:group_name/widget/:plugin_name/:widget_name' => array(
                'controller' => 'Group_Widget',
                'format' => array(':plugin_name' => '\w+', ':widget_name' => '\w+'),
                'access_callback' => true,
            ),
            '/groups/:group_name/members' => array(
                'controller' => 'Group_Members',
                'title' => $this->_('Members'),
                'type' => Plugg::ROUTE_TAB,
                'title_callback' => true,
            ),
            '/groups/:group_name/members/add' => array(
                'forward' => '/groups/:group_name/members/manage/add',
                'title' => $this->_('Add member'),
                'type' => Plugg::ROUTE_MENU,
                'access_callback' => true,
            ),
            '/groups/:group_name/members/invite' => array(
                'forward' => '/groups/:group_name/members/manage/invite',
                'title' => $this->_('Invite member'),
                'type' => Plugg::ROUTE_MENU,
                'access_callback' => true,
            ),
            '/groups/:group_name/members/manage' => array(
                'controller' => 'Group_Members_Manage',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Manage members'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/groups/:group_name/members/invited' => array(
                'controller' => 'Group_Members_Invited',
                'title' => $this->_('Invited members'),
                'type' => Plugg::ROUTE_TAB,
                'access_callback' => true,
            ),
            '/groups/:group_name/members/pending' => array(
                'controller' => 'Group_Members_Pending',
                'title' => $this->_('Pending members'),
                'type' => Plugg::ROUTE_TAB,
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/groups/:group_name/members/manage/add' => array(
                'controller' => 'Group_Members_Manage_Add',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Add member'),
            ),
            '/groups/:group_name/members/manage/invite' => array(
                'controller' => 'Group_Members_Manage_Invite',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Invite user'),
            ),
            '/groups/:group_name/leave' => array(
                'controller' => 'Group_Leave',
                'access_callback' => true,
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Leave group'),
            ),
            '/groups/:group_name/join' => array(
                'controller' => 'Group_Join',
                'access_callback' => true,
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Join group'),
            ),
            '/groups/:group_name/settings' => array(
                'controller' => 'Group_Settings',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Settings'),
                'access_callback' => true,
                'title_callback' => true,
                'weight' => 99,
            ),
            '/groups/:group_name/settings/widgets' => array(
                'controller' => 'Group_Settings_Widgets',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Widgets'),
            ),
            '/groups/:group_name/settings/widgets/submit' => array(
                'controller' => 'Group_Settings_Widgets_Submit',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/groups/:group_name/settings/widgets/:widget_id' => array(
                'format' => array(':widget_id' => '\d+'),
                'access_callback' => true,
            ),
            '/groups/:group_name/settings/widgets/:widget_id/edit' => array(
                'controller' => 'Group_Settings_Widgets_EditWidget',
            ),
            '/groups/:group_name/settings/delete' => array(
                'controller' => 'Group_Settings_Delete',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Delete'),
                'weight' => 10,
            ),
            '/groups/create' => array(
                'controller' => 'CreateGroup',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Create group'),
                'access_callback' => true,
            ),
            '/user/:user_id/groups' => array(
                'controller' => 'User_Index',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_nicename,
            )
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/groups/:group_name':
                if ((!$group_name = $request->asStr('group_name'))
                    || (!$this->_application->group = $this->getModel()->Group->criteria()->name_is(urlencode(urldecode($group_name)))->fetch()->getFirst())
                    || (!$this->_application->group->isApproved())
                ) return false;

                if (!$this->_application->getUser()->isAuthenticated()) {
                    $this->_application->membership = false;
                } else {
                    $this->_application->membership = $this->getModel()->Member->criteria()->groupId_is($this->_application->group->id)
                        ->userId_is($this->_application->getUser()->id)->fetch()->getFirst();
                }

                return true;

            case '/groups/:group_name/widget/:plugin_name/:widget_name':
                if ((!$plugin_name = $request->asStr('plugin_name'))
                    || (!$widget_name = $request->asStr('widget_name'))
                    || (!$plugin = $this->_application->getPlugin($plugin_name))
                ) return false;

                $widget = $this->getModel()->Widget->criteria()->plugin_is($plugin_name)
                    ->name_is($widget_name)->fetch()->getFirst();

                if (!$widget) return false;

                $this->_application->widget = $widget;
                $this->_application->widget_plugin = $plugin;

                return true;

            case '/groups/:group_name/leave':
                return $this->_application->membership
                    && $this->_application->membership->isActive() 
                    && !$this->_application->membership->isAdmin(); // group admins may not leave group

            case '/groups/:group_name/join':
                if (!$this->_application->getUser()->isAuthenticated()) {
                    return !$this->_application->group->isInvitationRequired();
                }

                if ($this->_application->membership) {
                    switch ($this->_application->membership->status) {
                        case self::MEMBER_STATUS_ACTIVE:
                            return false;
                        case self::MEMBER_STATUS_PENDING:
                            return false;
                        case self::MEMBER_STATUS_INVITED:
                            // User is invited to the group
                            return true;
                        default:
                            return false;
                    }
                }

                return !$this->_application->group->isInvitationRequired();

            case '/groups/:group_name/members/manage':
            case '/groups/:group_name/members/invite':
            case '/groups/:group_name/members/add':
            case '/groups/:group_name/settings':
            case '/groups/:group_name/members/invited':
            case '/groups/:group_name/members/pending':
                return $this->_application->getUser()->hasPermission('groups manage any')
                    || ($this->_application->membership && $this->_application->membership->isAdmin());

            case '/groups/:group_name/settings/widgets/:widget_id':
                return ($this->_application->widget = $this->getRequestedEntity($request, 'Widget')) ? true : false;
                
            case '/groups/create':
                return $this->_application->getUser()->isAuthenticated();
        }
    }

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/groups/:group_name':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Summary') : $this->_application->group->display_name;
                
            case '/groups/list':
                return $titleType === Plugg::ROUTE_TITLE_NORMAL ? $this->_('Groups list') : $title;
                
            case '/groups/:group_name/members':
                if ($titleType !== Plugg::ROUTE_TITLE_TAB) return $title;
                
                $this->_application->members_pending_count = $this->getModel()->Member->criteria()
                    ->groupId_is($this->_application->group->id)->status_is(self::MEMBER_STATUS_PENDING)->count();
                    
                return $this->_application->members_pending_count ? sprintf($this->_('Members (%d)'), $this->_application->members_pending_count) : $title;
                
            case '/groups/:group_name/members/pending':
                if ($titleType !== Plugg::ROUTE_TITLE_TAB) return $title;

                return $this->_application->members_pending_count ? sprintf($this->_('Pending members (%d)'), $this->_application->members_pending_count) : $title;
                
            case '/groups/:group_name/members/manage':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('List') : $title;
                
            case '/groups/:group_name/settings':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('General') : $title;
        }
    }

    /* End implementation of Plugg_System_Routable_Main */

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/groups' => array(
                'controller' => 'Index',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Group management'),
                'title_callback' => true,
            ),
            '/groups/pending' => array(
                'controller' => 'Pending',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Pending'),
                'title_callback' => true,
            ),
            '/groups/:group_id' => array(
                'controller' => 'Group',
                'format' => array(':group_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/groups/:group_id/add_member' => array(
                'forward' => 'members/add',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Add member')
            ),
            '/groups/:group_id/edit' => array(
                'controller' => 'Group_Edit',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Edit group')
            ),
            '/groups/:group_id/members' => array(
                'controller' => 'Group_Members',
            ),
            '/groups/:group_id/members/pending' => array(
                'controller' => 'Group_Members_Pending',
            ),
            '/groups/:group_id/members/invited' => array(
                'controller' => 'Group_Members_Invited',
            ),
            '/groups/:group_id/members/add' => array(
                'controller' => 'Group_Members_Add',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Add member'),
            ),
            '/groups/widgets' => array(
                'controller' => 'Widgets',
                'title' => $this->_('Widgets'),
                'type' => Plugg::ROUTE_TAB,
            ),
            '/groups/widgets/submit' => array(
                'controller' => 'Widgets_Submit',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/groups/widgets/:widget_id' => array(
                'format' => array(':widget_id' => '\d+'),
                'access_callback' => true,
            ),
            '/groups/widgets/:widget_id/edit' => array(
                'controller' => 'Widgets_EditWidget',
            ),
            '/groups/add' => array(
                'controller' => 'AddGroup',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Add group')
            ),
            '/groups/settings' => array(
                'controller' => 'Settings',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Settings'),
                'title_callback' => true,
                'weight' => 10,
            )
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/groups/:group_id':
                // Make sure the requested group exists
                return ($this->_application->group = $this->getRequestedEntity($request, 'Group', 'group_id')) ? true : false;

            case '/groups/widgets/:widget_id':
                // Make sure the requested widget exists
                return ($this->_application->widget = $this->getRequestedEntity($request, 'Widget', 'widget_id')) ? true : false;
        }
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/groups':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Groups') : $title;
                
            case '/groups/pending':
                if ($titleType === Plugg::ROUTE_TITLE_TAB) {
                    return sprintf(
                        $this->_('Pending (%d)'),
                        $this->getModel()->Group->criteria()->status_is(self::GROUP_STATUS_PENDING)->count()
                    );
                }
                
                return $title;

            case '/groups/:group_id':
                return $this->_application->group->display_name;

            case '/groups/settings':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('General') : $title;
        }
    }

    /* End implementation of Plugg_System_Routable_Admin */
    
    /* Start implementation of Plugg_User_Permissionable */

    public function userPermissionableGetPermissions()
    {
        return array(
            //'Article' => array(
                'groups create' => $this->_('Create a new group'),
                'groups edit any' => $this->_('Edit any group'),
                'groups delete any' => $this->_('Delete any group'),
                'groups approve' => $this->_('Approve newly created groups'),
            //),
        );
    }

    public function userPermissionableGetDefaultPermissions()
    {
        return array('groups create');
    }
    
    /* End implementation of Plugg_User_Permissionable */

    public function onUserIdentityDeleteSuccess($identity)
    {
        $model = $this->getModel();
        $id = $identity->id;

        // Remove membership data of the user
        foreach ($model->Member->fetchByUser($id) as $membership) {
            $membership->markRemoved();
        }
        $model->commit();
    }

    /* Start implementation of Plugg_Widgets_Widget */

    public function widgetsGetWidgetNames()
    {
        return array('groups' => Plugg_Widgets_Plugin::WIDGET_TYPE_CACHEABLE);
    }

    public function widgetsGetWidgetTitle($widgetName)
    {
        return $this->_nicename;
    }

    public function widgetsGetWidgetSummary($widgetName)
    {
        return $this->_('Displays available community groups.');
    }

    public function widgetsGetWidgetSettings($widgetName, array $currentValues = array())
    {
        return array(
            'limit' => array(
                '#type' => 'radios',
                '#title' => $this->_('Number of groups to display'),
                '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10),
                '#delimiter' => '&nbsp;',
                '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 5,
            ),
            'sort' => array(
                '#type' => 'radios',
                '#title' => $this->_('Default display order'),
                '#options' => array('newest' => $this->_('Newest first'), 'popular' => $this->_('Popularity')),
                '#delimiter' => '<br />',
                '#default_value' => isset($currentValues['sort']) ? $currentValues['sort'] : 'newest',
            ),
        );
    }

    public function widgetsGetWidgetContent($widgetName, $widgetSettings, Sabai_User $user)
    {
        $data = array(
            'menu' => array(
                array(
                    'text' => $this->_('Newest'),
                    'settings' => array('sort' => 'newest')
                ),
                array(
                    'text' => $this->_('Popular'),
                    'settings' => array('sort' => 'popular'),
                ),
            )
        );

        switch ($widgetSettings['sort']) {
            case 'popular':
                $data['menu'][1]['current'] = true;
                $data['content'] = $this->_renderPopularGroupsWidget($widgetSettings);
                break;
            default:
                $data['menu'][0]['current'] = true;
                $data['content'] = $this->_renderNewGroupsWidget($widgetSettings);
        }

        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => array('base' => '/groups'),
                    'title' => sprintf($this->_('Show all (%d)'), $this->getModel()->Group->criteria()->status_is(self::GROUP_STATUS_APPROVED)->count()), 
                )
            );
        }

        return $data;
    }

    private function _renderPopularGroupsWidget($settings)
    {
        $content = array();
        $member_count = $this->getModel()->getGateway('Group')->getPopularGroupMemberCount($settings['limit']);
        if (count($member_count) > 0) {
            $groups = $this->getModel()->Group
                ->criteria()
                ->id_in(array_keys($member_count))
                ->fetch()
                ->with('User')
                ->getArray();
            foreach (array_keys($member_count) as $group_id) {
                $group = $groups[$group_id];
                $content[] = array(
                    'title' => $group->display_name,
                    'url' => $group_url = $group->getUrl(),
                    'body' => $group->description_html,
                    'thumbnail' => $group->getAvatarThumbnailUrl(),
                    'thumbnail_link' => $group_url,
                    'thumbnail_title' => $group->display_name,
                    'timestamp' => $group->created,
                    'timestamp_label' => $this->_('Created'),
                    'meta_html' => array(sprintf($this->_n('1 member', '%d members', $member_count[$group_id]), $member_count[$group_id]))
                );
            }
        }

        return $content;
    }

    private function _renderNewGroupsWidget($settings)
    {
        $content = array();
        $groups = $this->getModel()->Group
            ->criteria()
            ->status_is(self::GROUP_STATUS_APPROVED)
            ->fetch($settings['limit'], 0, 'created', 'DESC')
            ->with('User')
            ->with('MemberCount');
        foreach ($groups as $group) {
            $content[] = array(
                'title' => $group->display_name,
                'url' => $group_url = $group->getUrl(),
                'body' => $group->description_html,
                'thumbnail' => $group->getAvatarThumbnailUrl(),
                'thumbnail_link' => $group_url,
                'thumbnail_title' => $group->display_name,
                'timestamp' => $group->created,
                'timestamp_label' => $this->_('Created'),
                'meta_html' => array(sprintf($this->_n('1 member', '%d members', $group->getCount('ActiveMember')), $group->getCount('ActiveMember')))
            );
        }

        return $content;
    }
    /* End implementation of Plugg_Widgets_Widget */

    /* Start implementation of Plugg_AdminWidget_Widget*/

    public function adminWidgetGetNames()
    {
        return array(
            'new_groups' => Plugg_AdminWidget_Plugin::WIDGET_TYPE_CACHEABLE,
        );
    }

    public function adminWidgetGetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'new_groups':
                return $this->_('New groups');
        }
    }

    public function adminWidgetGetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'new_groups':
                return $this->_('Displays recently created groups.');
        }
    }

    public function adminWidgetGetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'new_groups':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of groups to display'),
                        '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10),
                        '#delimiter' => '&nbsp;',
                        '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 5,
                    ),
                    'type' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Default display type:'),
                        '#options' => array(
                            'all' => $this->_('Display all groups'),
                            'approved' => $this->_('Approved groups'),
                            'pending' => $this->_('Pending groups'),
                        ),
                        '#delimiter' => '<br />',
                        '#default_value' => isset($currentValues['type']) ? $currentValues['type'] : 'all',
                    ),
                );
        }

        return array();
    }

    public function adminWidgetGetContent($widgetName, $widgetSettings)
    {
        switch ($widgetName) {
            case 'new_groups':
                return $this->_renderNewGroupsAdminWidget($widgetSettings);
        }
    }

    private function _renderNewGroupsAdminWidget($settings)
    {
        $data = array();
        $data['menu'] = array(
            array(
                'text' => $this->_('All groups'),
                'settings' => array('type' => 'all'),
            ),
            array(
                'text' => $this->_('Approved'),
                'settings' => array('type' => 'approved'),
            ),
            array(
                'text' => $this->_('Pending'),
                'settings' => array('type' => 'pending'),
            ),
        );

        $criteria = $this->getModel()->createCriteria('Group');
        switch ($settings['type']) {
            case 'approved':
                $data['menu'][1]['current'] = true;
                $criteria->status_is(self::GROUP_STATUS_APPROVED);
                break;
            case 'pending':
                $data['menu'][2]['current'] = true;
                $criteria->status_is(self::GROUP_STATUS_PENDING);
                break;
            default:
                $data['menu'][0]['current'] = true;
                break;
        }

        $groups = $this->getModel()->Group
            ->fetchByCriteria($criteria, $settings['limit'], 0, 'created', 'DESC')
            ->with('User')
            ->with('MemberCount');
        foreach ($groups as $group) {
            $data['content'][] = array(
                'title' => $group->display_name,
                'url' => $group_url = $group->getUrl(),
                'body' => $group->description_html,
                'thumbnail' => $group->getAvatarThumbnailUrl(),
                'thumbnail_link' => $group_url,
                'thumbnail_title' => $group->display_name,
                'timestamp' => $group->created,
                'user' => $group->User,
                'meta_html' => array(sprintf($this->_n('1 member', '%d members', $group->getCount('ActiveMember')), $group->getCount('ActiveMember')))
            );
        }

        return $data;
    }

    /* End implementation of Plugg_AdminWidget_Widget*/

    /* Start implementation of Plugg_User_Widget */

    public function userWidgetGetNames()
    {
        return array(
            'groups' => Plugg_User_Plugin::WIDGET_TYPE_PUBLIC | Plugg_User_Plugin::WIDGET_TYPE_CACHEABLE,
        );
    }

    public function userWidgetGetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'groups':
                return $this->_('Groups');
        }
    }

    public function userWidgetGetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'groups':
                return $this->_('Displays a list of groups to which the user belongs.');
        }
    }

    public function userWidgetGetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'groups':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of groups to display'),
                        '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10, 15 => 15, 20 => 20),
                        '#delimiter' => '&nbsp;',
                        '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 10,
                    ),
                );
        }
    }

    public function userWidgetGetContent($widgetName, $widgetSettings, Sabai_User_Identity $identity, $isOwner, $isAdmin)
    {
        switch ($widgetName) {
            case 'groups':
                return $this->_renderGroupsUserWidget($widgetSettings, $identity);
        }
    }

    private function _renderGroupsUserWidget($settings, $identity)
    {
        $id = $identity->id;
        $count = $this->getModel()->Member->countByUser($id);
        if ($count <= 0) return;

        $memberships = $this->getModel()->Member->fetchByUser($id, $settings['limit'])->with('Group', 'MemberCount');
        foreach ($memberships as $membership) {
            $group = $membership->Group;
            $data['content'][] = array(
                'title' => $group->display_name,
                'url' => $group_url = $group->getUrl(),
                'body' => $group->description_html,
                'thumbnail' => $group->getAvatarThumbnailUrl(),
                'thumbnail_link' => $group_url,
                'thumbnail_title' => $group->display_name,
                'timestamp' => $membership->created,
                'timestamp_label' => $this->_('Member since'),
                'meta_html' => array(sprintf($this->_n('1 member', '%d members', $group->getCount('ActiveMember')), $group->getCount('ActiveMember')))
            );
        }

        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => $this->_application->User_IdentityUrl($identity, 'groups'),
                    'title' => sprintf($this->_('Show all (%d)'), $count)
                )
            );
        }

        return $data;
    }

    /* End implementation of Plugg_User_Widget */

    /* Start implementation of Plugg_Groups_Widget */

    public function groupsWidgetGetNames()
    {
        return array('members' => self::WIDGET_TYPE_CACHEABLE);
    }

    public function groupsWidgetGetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'members':
                return $this->_('Members');
        }
    }

    public function groupsWidgetGetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'members':
                return $this->_('Displays a list of group members.');
        }
    }

    public function groupsWidgetGetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'members':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of members to display'),
                        '#options' => array(7 => 7, 10 => 10, 20 => 20),
                        '#delimiter' => '&nbsp;',
                        '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 10,
                    ),
                    'type' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Default display type'),
                        '#options' => array(
                            'all' => $this->_('All members'),
                            'admins' => $this->_('Group administrators'),
                            'members' => $this->_('Non-admin members')
                        ),
                        '#delimiter' => '<br />',
                        '#default_value' => isset($currentValues['type']) ? $currentValues['limit'] : 'all',
                    ),
                );
        }
    }

    function groupsWidgetGetContent($widgetName, $widgetSettings, Plugg_Groups_Model_Group $group, $isMemberViewing, $isAdminViewing)
    {
        switch ($widgetName) {
            case 'members':
                return $this->_renderMembersGroupsWidget($widgetSettings, $group);
        }
    }

    private function _renderMembersGroupsWidget($settings, $group)
    {
        $data= array(
            'menu' => array(
                array(
                    'text' => $this->_('All members'),
                    'settings' => array('type' => 'all'),
                    'current' => !in_array($settings['type'], array('admins', 'members'))
                ),
                array(
                    'text' => $this->_('Admins'),
                    'settings' => array('type' => 'admins'),
                    'current' => $settings['type'] == 'admins'
                ),
                array(
                    'text' => $this->_('Non admins'),
                    'settings' => array('type' => 'members'),
                    'current' => $settings['type'] == 'members'
                ),
            )
        );

        $criteria = $this->getModel()->createCriteria('Member')
            ->status_is(self::MEMBER_STATUS_ACTIVE);
        if ($settings['type'] == 'members') {
            $criteria = $criteria->role_isNot(self::MEMBER_ROLE_ADMINISTRATOR);
        } elseif ($settings['type'] == 'admins') {
            $criteria = $criteria->role_is(self::MEMBER_ROLE_ADMINISTRATOR);
        }
        $count = $this->getModel()->Member->countByGroupAndCriteria($group->id, $criteria);
        if ($count <= 0) return $data;

        $members = $this->getModel()->Member
            ->fetchByGroupAndCriteria($group->id, $criteria, $settings['limit'], 0, 'created', 'DESC')
            ->with('User');

        foreach ($members as $member) {
            $identity = $member->User;
            if ($identity->isAnonymous()) continue;
            $data['content'][] = array(
                'title' => $identity->display_name,
                'url' => $user_url = $this->_application->User_IdentityUrl($identity),
                'thumbnail' => $identity->image_thumbnail,
                'thumbnail_link' => $user_url,
                'thumbnail_title' => $identity->display_name,
                'timestamp' => $member->created,
                'timestamp_label' => $this->_('Member since')
            );
        }

        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => $group->getUrl('members'),
                    'title' => sprintf($this->_('Show all (%d)'), $count)
                )
            );
        }


        return $data;
    }

    /* End implementation of Plugg_Groups_Widget */


    public function onGroupsWidgetInstalled($pluginEntity, $plugin)
    {
        if ($widgets = $plugin->groupsWidgetGetNames()) {
            $model = $this->getModel();
            $this->_createPluginGroupsWidgets($model->create('Widget'), $pluginEntity->name, $widgets);
            $model->commit();
        }
    }

    public function onGroupsWidgetUninstalled($pluginEntity, $plugin)
    {
        $this->_deletePlugingroupsWidgets($pluginEntity->name);
    }

    public function onGroupsWidgetUpgraded($pluginEntity, $plugin)
    {
        if (!$widgets = $plugin->groupsWidgetGetNames()) {
            $this->_deletePlugingroupsWidgets($pluginEntity->name);
        } else {
            $model = $this->getModel();
            $widgets_already_installed = array();
            foreach ($model->Widget->criteria()->plugin_is($pluginEntity->name)->fetch() as $current_widget) {
                if (in_array($current_widget->name, $widgets)) {
                    $widgets_already_installed[] = $current_widget->name;
                    if ($type = @$widgets[$current_widget->name]) {
                        $current_widget->type = $type; // Update the widget type if configured explicitly
                    }
                } else {
                    $current_widget->markRemoved();
                }
            }
            $this->_createPluginGroupsWidgets(
                $model->create('Widget'),
                $pluginEntity->name,
                array_diff($widgets, $widgets_already_installed)
            );
            $model->commit();
        }
    }

    private function _createPluginGroupsWidgets($prototype, $pluginName, $widgets)
    {
        foreach ($widgets as $widget_name => $widget_type) {
            if (empty($widget_name)) continue;
            $widget = clone $prototype;
            $widget->name = $widget_name;
            $widget->plugin = $pluginName;
            $widget->type = $widget_type;
            $widget->markNew();
            
            // Activate widget
            $widget_plugin = $this->_application->getPlugin($pluginName);
            $active_widget = $widget->createActivewidget();
            $active_widget->title = $widget_plugin->groupsWidgetGetTitle($widget_name);
            $settings = array();
            if ($widget_settings = $widget_plugin->groupsWidgetGetSettings($widget_name)) {
                foreach ($widget_settings as $k => $setting) {
                    if (isset($setting['#default_value'])) {
                        $settings[$k] = $setting['#default_value'];
                    }
                }
            }
            $active_widget->settings = serialize($settings);
            $active_widget->position = self::WIDGET_POSITION_LEFT;
            $active_widget->order = 99;
            $active_widget->markNew();
        }
    }

    private function _deletePlugingroupsWidgets($pluginName)
    {
        $model = $this->getModel();
        foreach ($model->Widget->criteria()->plugin_is($pluginName)->fetch() as $widget) {
            $widget->markRemoved();
        }

        return $model->commit();
    }

    public function getGroupTypes()
    {
        return array(
            self::GROUP_TYPE_APPROVAL_REQUIRED => $this->_('Approval required'),
            self::GROUP_TYPE_INVITATION_REQUIRED => $this->_('Invitation required'),
            self::GROUP_TYPE_NONE => $this->_('Anyone can join')
        );
    }

    public function sendJoinGroupRequestApprovedEmail(Plugg_Groups_Model_Group $group, Sabai_User_Identity $user)
    {
        if (!$this->getConfig('joinRequestApprovedEmail', 'enable')) return;

        $tags = $this->getEmailTags($group, $user);
        return $this->_application->getPlugin('Mail')->getSender()
            ->mailSend(
                array($user->email, $user->username),
                strtr($this->getConfig('joinRequestApprovedEmail', 'subject'), $tags),
                strtr($this->getConfig('joinRequestApprovedEmail', 'body'), $tags)
            );
    }

    public function sendGroupInvitationEmail(Plugg_Groups_Model_Group $group, Sabai_User_Identity $user)
    {
        $tags = $this->getEmailTags($group, $user);
        return $this->_application->getPlugin('Mail')->getSender()
            ->mailSend(
                array($user->email, $user->username),
                strtr($this->getConfig('invitationEmail', 'subject'), $tags),
                strtr($this->getConfig('invitationEmail', 'body'), $tags)
            );
    }

    public function getEmailTags($group, $user)
    {
        return array(
            '{SITE_NAME}' => $site_name = $this->_application->SiteName(),
            '{SITE_URL}' => $this->_application->SiteUrl(),
            '{USER_NAME}' => $user->username,
            '{USER_EMAIL}'=> $user->email,
            '{GROUP_NAME}' => $group->display_name,
            '{GROUP_DESCRIPTION}' => $group->getSummary(),
            '{GROUP_MAIN_URL}' =>$group->getUrl(),
            '{GROUP_JOIN_URL}' => $group->getUrl('join/' . $group->id, array(), '&'),
        );
    }

    public function getWidgetData()
    {
        // Fetch available widgets and data
        $widgets = array();
        foreach ($this->getModel()->Widget->fetch(0, 0, 'plugin', 'ASC') as $widget) {
            // skip if plugin of the widget is not enabled
            if (!$widget_plugin = $this->_application->getPlugin($widget->plugin)) continue;
            if (!$widget_plugin instanceof Plugg_User_Widget) continue;

            $widgets[$widget->id] = array(
                'id' => $widget->id,
                'name' => $widget->name,
                'title' => $widget_plugin->groupsWidgetGetTitle($widget->name),
                'summary' => $widget_plugin->groupsWidgetGetSummary($widget->name),
                'settings' => $widget_plugin->groupsWidgetGetSettings($widget->name),
                'plugin' => $widget_plugin->nicename,
            );
        }

        return $widgets;
    }

    public function getActiveWidgets($groupId = 0)
    {
        return $this->getModel()->Activewidget
            ->criteria()
            ->groupId_is($groupId)
            ->fetch(0, 0, 'order', 'ASC');
    }
    
    public function getDefaultConfig()
    {
        return array(
            'joinRequestApprovedEmail' => array(
                'enable' => true,
                'subject' => $this->_('You are now a member of the group {GROUP_NAME} at {SITE_NAME}'),
                'body' => implode(PHP_EOL . PHP_EOL, array(
                    $this->_('Hello {USER_NAME},'),
                    $this->_('You are now a member of the group {GROUP_NAME} at {SITE_NAME}.'),
                    $this->_('Visit the group page at the following URL:'),
                    '{GROUP_MAIN_URL}',
                    '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}',
                ))
            ),
            'invitationEmail' => array(
                'subject' => $this->_('You have been invited to the group {GROUP_NAME} at {SITE_NAME}'),
                'body' => implode(PHP_EOL . PHP_EOL, array(
                    $this->_('Hello {USER_NAME},'),
                    $this->_('You have been invited to the group {GROUP_NAME} at {SITE_NAME}.'),
                    $this->_('To accept invitation and join the group, click on the link below:'),
                    '{GROUP_JOIN_URL}',
                    '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}',
                ))
            ),
        );
    }
}
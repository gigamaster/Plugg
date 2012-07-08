<?php
class Plugg_Aggregator_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_System_Routable_Admin, Plugg_User_Permissionable, Plugg_User_Widget, Plugg_Widgets_Widget, Plugg_AdminWidget_Widget/*, Plugg_Search_Searchable */
{
    const FEED_STATUS_PENDING = 0;
    const FEED_STATUS_APPROVED = 1;

    const FEED_AUTHOR_PREF_BLOG_OWNER = 0;
    const FEED_AUTHOR_PREF_ENTRY_AUTHOR_OWNER = 1;
    const FEED_AUTHOR_PREF_ENTRY_AUTHOR = 2;

    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/aggregator' => array(
                'controller' => 'Index',
                'title' => $this->_nicename,
                'title_callback' => true,
                'type' => Plugg::ROUTE_TAB,
            ),
            '/aggregator/feeds' => array(
                'controller' => 'Feeds',
                'type' => Plugg::ROUTE_TAB,
                'ajax' => false,
                'title' => $this->_('Feeds')
            ),
            '/aggregator/feeds/new' => array(
                'controller' => 'Feeds_NewFeed',
                'type' => Plugg::ROUTE_MENU,
                'ajax' => true,
                'title' => $this->_('Add feed'),
                'access_callback' => true,
            ),
            '/aggregator/feeds/:feed_id' => array(
                'controller' => 'Feeds_Feed',
                'format' => array(':feed_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/aggregator/feeds/:feed_id/edit' => array(
                'controller' => 'Feeds_Feed_Edit',
                'title' => $this->_('Edit feed'),
            ),
            '/aggregator/feeds/:feed_id/remove' => array(
                'controller' => 'Feeds_Feed_Remove',
                'title' => $this->_('Remove feed'),
            ),
            '/aggregator/rss' => array(
                'controller' => 'RSS',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('RSS'),
                'ajax' => false
            ),
            '/aggregator/:feed_id' => array(
                'controller' => 'Feed',
                'format' => array(':feed_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/aggregator/:feed_id/rss' => array(
                'controller' => 'Feed_RSS',
                'title' => $this->_('RSS'),
                'type' => Plugg::ROUTE_MENU,
                'ajax' => false
            ),
            '/aggregator/:feed_id/ping' => array(
                'controller' => 'Feed_Ping',
                'callback' => true
            ),
            '/aggregator/:feed_id/:item_id' => array(
                'controller' => 'Feed_Item',
                'format' => array(':item_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/aggregator/:feed_id/:item_id/delete' => array(
                'controller' => 'Feed_Item_Delete',
                'title' => $this->_('Delete feed item'),
            ),
            '/aggregator/:feed_id/:item_id/edit' => array(
                'controller' => 'Feed_Item_Edit',
                'title' => $this->_('Edit feed item'),
            ),
            '/user/:user_id/aggregator' => array(
                'controller' => 'User_Index',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Feeds'),
            ),
            '/user/:user_id/aggregator/new' => array(
                'controller' => 'User_NewFeed',
                'type' => Plugg::ROUTE_MENU,
                'ajax' => true,
                'title' => $this->_('Add feed'),
                'access_callback' => true,
            ),
            '/user/:user_id/aggregator/:feed_id' => array(
                'controller' => 'User_Feed',
                'format' => array(':feed_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/user/:user_id/aggregator/:feed_id/edit' => array(
                'controller' => 'User_Feed_Edit',
            ),
            '/user/:user_id/aggregator/:feed_id/remove' => array(
                'controller' => 'User_Feed_Remove',
            ),
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/aggregator/feeds/new':
                return $this->_application->getUser()->hasPermission(array(
                    'aggregator feed add any', 'aggregator feed add any approved'
                ));

            case '/aggregator/:feed_id':
            case '/aggregator/feeds/:feed_id':
                return ($this->_application->feed = $this->getRequestedEntity($request, 'Feed', 'feed_id')) ? true : false;

            case '/aggregator/:feed_id/:item_id':
                return ($this->_application->feed_item = $this->getRequestedEntity($request, 'Item', 'item_id'))
                    && $this->_application->feed_item->feed_id == $this->_application->feed->id;

            case '/user/:user_id/aggregator/new':
                return $this->_application->getUser()->hasPermission(array('aggregator feed add any', 'aggregator feed add any approved'))
                    || ($this->_application->identity_is_me
                        && $this->_application->getUser()->hasPermission(array(
                            'aggregator feed add own', 'aggregator feed add own approved')));

            case '/user/:user_id/aggregator/:feed_id':
                return ($this->_application->feed = $this->getRequestedEntity($request, 'Feed', 'feed_id'))
                    && $this->_application->feed->isOwnedBy($this->_application->identity);
        }
    }

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/aggregator':
            case '/user/:user_id/aggregator':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Articles') : $title;

            case '/aggregator/:feed_id':
            case '/aggregator/feeds/:feed_id':
            case '/user/:user_id/aggregator/:feed_id':
                return $this->_application->feed->title;

            case '/aggregator/:feed_id/:item_id':
                return $this->_application->feed_item->title;
        }
    }

    /* Start implementation of Plugg_System_Routable_Main */

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/content/aggregator' => array(
                'controller' => 'Index',
                'title' => $this->_nicename,
                'title_callback' => true,
                'type' => Plugg::ROUTE_TAB,
            ),
            '/content/aggregator/feeds' => array(
                'controller' => 'Feeds',
                'type' => Plugg::ROUTE_TAB,
                'ajax' => true,
                'title' => $this->_('Feeds'),
                'title_callback' => true,
            ),
            '/content/aggregator/feeds/pending' => array(
                'controller' => 'Feeds_Pending',
                'title' => $this->_('Pending'),
                'type' => Plugg::ROUTE_TAB,
            ),
            '/content/aggregator/feeds/add' => array(
                'controller' => 'Feeds_Add',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Add feed'),
                'ajax' => true,
            ),
            '/content/aggregator/feeds/:feed_id' => array(
                'controller' => 'Feeds_Feed',
                'format' => array(':feed_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/content/aggregator/feeds/:feed_id/items' => array(
                'controller' => 'Feeds_Feed_Items',
            ),
            '/content/aggregator/feeds/:feed_id/edit' => array(
                'controller' => 'Feeds_Feed_Edit',
                'type' => Plugg::ROUTE_MENU,
                'ajax' => true,
                'title' => $this->_('Edit feed')
            ),
            '/content/aggregator/feeds/:feed_id/:item_id' => array(
                'format' => array(':item_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/content/aggregator/feeds/:feed_id/:item_id/edit' => array(
                'controller' => 'Feeds_Feed_Item_Edit',
            ),
            '/content/aggregator/settings' => array(
                'controller' => 'Settings',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Settings'),
                'title_callback' => true,
                'weight' => 30,
            ),
            '/content/aggregator/settings/email' => array(
                'controller' => 'Settings_Email',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Email settings'),
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/content/aggregator/feeds/:feed_id':
                return ($this->_application->feed = $this->getRequestedEntity($request, 'Feed', 'feed_id')) ? true : false;

            case '/content/aggregator/feeds/:feed_id/:item_id':
                return ($this->_application->feed_item = $this->getRequestedEntity($request, 'Item', 'item_id'))
                    && $this->_application->feed_item->feed_id == $this->_application->feed->id;
        }
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/content/aggregator':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Articles') : $title;
                
            case '/content/aggregator/feeds':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('List') : $title;

            case '/content/aggregator/feeds/:feed_id':
                return $this->_application->feed->title;
                
            case '/content/aggregator/feeds/:feed_id/:item_id':
                return $this->_application->feed_item->title;

            case '/content/aggregator/settings':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('General') : $title;
        }
    }

    /* End implementation of Plugg_System_Routable_Admin */
    
    /* Start implementation of Plugg_User_Permissionable */

    public function userPermissionableGetPermissions()
    {
        return array(
            //'Feed' => array(
                'aggregator feed add own' => $this->_('Add own feed'),
                'aggregator feed add own approved' => $this->_('Add own feed approved by default'),
                'aggregator feed add any' => $this->_('Add feed for any user'),
                'aggregator feed add any approved' => $this->_('Add feed for any user, approved by default'),
                'aggregator feed edit own' => $this->_('Edit own feed data'),
                'aggregator feed edit any' => $this->_('Edit any feed data'),
                'aggregator feed allow own img' => $this->_('Allow images in own feed'),
                'aggregator feed allow any img' => $this->_('Allow images in any feed'),
                'aggregator feed allow own ex resources' => $this->_('Allow external resources in own feed'),
                'aggregator feed allow any ex resources' => $this->_('Allow external resources in any feed'),
                'aggregator feed edit own host' => $this->_('Edit host name of own feed'),
                'aggregator feed edit any host' => $this->_('Edit host name of any feed'),
                'aggregator feed delete own' => $this->_('Delete own feed'),
                'aggregator feed delete any' => $this->_('Delete any feed'),
            //),
            //'Item' => array(
                'aggregator item edit own' => $this->_('Edit own feed item'),
                'aggregator item edit any' => $this->_('Edit any feed item'),
                'aggregator item edit own body' => $this->_('Edit body of own feed item'),
                'aggregator item edit any body' => $this->_('Edit body of any feed item'),
                'aggregator item edit own author' => $this->_('Edit author name of own feed item'),
                'aggregator item edit any author' => $this->_('Edit author name of any feed item'),
                'aggregator item edit own author link' => $this->_('Edit author link of own feed item'),
                'aggregator item edit any author link' => $this->_('Edit author link of any feed item'),
                'aggregator item hide own' => $this->_('Hide own feed item'),
                'aggregator item hide any' => $this->_('Hide any feed item'),
                'aggregator item delete own' => $this->_('Delete own feed item'),
                'aggregator item delete any' => $this->_('Delete any feed item'),
            //),
        );
    }

    public function userPermissionableGetDefaultPermissions()
    {
        return array(
            'aggregator feed add own',
            'aggregator feed edit own',
            'aggregator feed allow own img',
            'aggregator feed delete own',
            'aggregator item edit own',
            'aggregator item edit own author',
            'aggregator item edit own author link',
            'aggregator item hide own',
            'aggregator item delete own'
        );
    }
    
    /* End implementation of Plugg_User_Permissionable */

    public function onPluggCron($lastrun, array $logs)
    {
        // Allow run this cron 1 time per day at most
        if (!empty($lastrun) && time() - $lastrun < 86400) return;

        if (!$cron_days = intval($this->getConfig('cronIntervalDays'))) return;

        // Get feeds where the last cron time is older than specified amount of days or the latest ping is newer than the last cron time
        $feeds = $this->getModel()->Feed
            ->criteria()
            ->status_is(self::FEED_STATUS_APPROVED)
            ->add($this->getModel()->createCriteria('Feed')
                ->lastFetch_isSmallerThan(time() - ($cron_days * 86400))
                ->or_()
                ->lastPing_isGreaterThan_lastFetch())
            ->fetch();

        foreach ($feeds as $feed) {
            $logs[] = sprintf($this->_('Updating aggregator feed: %s.'), $feed->title);
            $this->loadFeedItems($feed);
        }
    }

    public function onUserIdentityDeleteSuccess($identity)
    {
        $feeds = $this->getModel()->Feed->fetchByUser($identity->id);

        foreach ($feeds as $feed) {
            $feed->markRemoved();
        }

        $this->getModel()->commit();
    }
    
    /* Start implementation of Plugg_AdminWidget_Widget*/

    public function adminWidgetGetNames()
    {
        return array(
            'feeds' => Plugg_AdminWidget_Plugin::WIDGET_TYPE_CACHEABLE,
        );
    }

    public function adminWidgetGetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'feeds':
                return $this->_('New feeds');
        }
    }

    public function adminWidgetGetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'feeds':
                return $this->_('Displays recently added feeds.');
        }
    }

    public function adminWidgetGetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'feeds':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of feeds to display'),
                        '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10),
                        '#delimiter' => '&nbsp;',
                        '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 5,
                    ),
                    'type' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Default display type:'),
                        '#options' => array(
                            'all' => $this->_('Display all feeds'),
                            'approved' => $this->_('Approved feeds'),
                            'pending' => $this->_('Pending feeds'),
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
            case 'feeds':
                return $this->_renderFeedsAdminWidget($widgetSettings);
        }
    }

    private function _renderFeedsAdminWidget($settings)
    {
        $data = array();
        $data['menu'] = array(
            array(
                'text' => $this->_('All feeds'),
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

        $criteria = $this->getModel()->createCriteria('Feed');
        switch ($settings['type']) {
            case 'approved':
                $data['menu'][1]['current'] = true;
                $criteria->status_is(self::FEED_STATUS_APPROVED);
                break;
            case 'pending':
                $data['menu'][2]['current'] = true;
                $criteria->status_is(self::FEED_STATUS_PENDING);
                break;
            default:
                $data['menu'][0]['current'] = true;
                break;
        }

        $feeds = $this->getModel()->Feed
            ->fetchByCriteria($criteria, $settings['limit'], 0, 'created', 'DESC');
        foreach ($feeds as $feed) {
            $data['content'][] = array(
                'title' => $feed->display_name,
                   'body' => $feed->description,
                'url' => array('base' => '/aggregator', 'path' => $feed->id),
                'thumbnail' => $feed->getScreenshotUrl(48, 48),
                'timestamp' => $feed->last_publish ? $feed->last_publish : $feed->created,
                'timestamp_label' => $this->_('Last update'),
                'meta_html' => array(sprintf($this->_n('1 article', '%d articles', $feed->item_count), $feed->item_count)),
            );
        }

        return $data;
    }

    /* End implementation of Plugg_AdminWidget_Widget*/


    /* Start implementation of Plugg_Widgets_Widget */

    public function widgetsGetWidgetNames()
    {
        return array(
            'feeds' => Plugg_Widgets_Plugin::WIDGET_TYPE_CACHEABLE,
            'new_items' => Plugg_Widgets_Plugin::WIDGET_TYPE_CACHEABLE
        );
    }

    public function widgetsGetWidgetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'feeds':
                return $this->_('Feeds');
            case 'new_items':
                return $this->_('Recent articles');
        }
    }

    public function widgetsGetWidgetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'feeds':
                return $this->_('Displays registered feeds.');
            case 'new_items':
                return $this->_('Displays recently published feed articles.');
        }
    }

    public function widgetsGetWidgetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'feeds':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of feeds to display'),
                        '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10),
                        '#delimiter' => '&nbsp;',
                        '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 5,
                    ),
                    'sort' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Sort feeds by:'),
                        '#options' => array('newest' => $this->_('Newest first'), 'active' => $this->_('Last updated first')),
                        '#delimiter' => '<br />',
                        '#default_value' => isset($currentValues['sort']) ? $currentValues['sort'] : 'newest',
                    ),
                );
            case 'new_items':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of recently published articles to display'),
                        '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10, 15 => 15, 20 => 20, 30 => 30),
                        '#delimiter' => '&nbsp;',
                        '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 10,
                    )
                );
        }
    }

    public function widgetsGetWidgetContent($widgetName, $widgetSettings, Sabai_User $user)
    {
        switch ($widgetName) {
            case 'feeds':
                return $this->_renderFeedsWidget($widgetSettings);
            case 'new_items':
                return $this->_renderNewItemsWidget($widgetSettings);
        }
    }

    private function _renderFeedsWidget($widgetSettings)
    {
        $data = array();
        $data['menu'] = array(
            array(
                'text' => $this->_('Newest'),
                'settings' => array('sort' => 'newest'),
            ),
            array(
                'text' => $this->_('Active'),
                'settings' => array('sort' => 'active'),
            ),
        );

        $model = $this->getModel();
        if ($widgetSettings['sort'] == 'active') {
            $feeds = $model->Feed
                ->criteria()
                ->status_is(self::FEED_STATUS_APPROVED)
                ->fetch($widgetSettings['limit'], 0, 'last_published', 'DESC');
            foreach ($feeds as $feed) {
                $data['content'][] = array(
                    'title' => $feed->title,
                    'body' => $feed->description,
                    'url' => array('base' => '/aggregator', 'path' => $feed->id),
                    'thumbnail' => $feed->getScreenshotUrl(48, 48),
                    'timestamp' => $feed->last_publish ? $feed->last_publish : $feed->created,
                    'timestamp_label' => $this->_('Last update'),
                    'meta_html' => array(sprintf($this->_n('1 article', '%d articles', $feed->item_count), $feed->item_count))
                );
            }
            $data['menu'][1]['current'] = true;
        } else {
            $feeds = $model->Feed
                ->criteria()
                ->status_is(self::FEED_STATUS_APPROVED)
                ->fetch($widgetSettings['limit'], 0, 'created', 'DESC')
                ->with('User');
            foreach ($feeds as $feed) {
                $data['content'][] = array(
                    'title' => $feed->title,
                    'body' => $feed->description,
                    'url' => array('base' => '/aggregator', 'path' => $feed->id),
                    'thumbnail' => $feed->getScreenshotUrl(48, 48),
                    'timestamp' => $feed->created,
                    'user' => !$feed->User->isAnonymous() ? $feed->User : '',
                    'meta_html' => array(sprintf($this->_n('1 article', '%d articles', $feed->item_count), $feed->item_count))
                );
            }
            $data['menu'][0]['current'] = true;
        }

        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => array('base' => '/aggregator', 'path' => 'feeds'),
                    'title' => sprintf($this->_('Show all (%d)'), $model->Feed->criteria()->status_is(self::FEED_STATUS_APPROVED)->count())
                )
            );
        }

        return $data;
    }

    private function _renderNewItemsWidget($widgetSettings)
    {
        $data = array();
        $model = $this->getModel();
        $items = $model->Item
            ->criteria()
            ->hidden_is(0)
            ->fetch($widgetSettings['limit'], 0, 'published', 'DESC')
            ->with('Feed', 'User');
        foreach ($items as $item) {
            $item_user = null;
            $item_meta = array();
            if ($item->author && $item->Feed->author_pref == self::FEED_AUTHOR_PREF_ENTRY_AUTHOR) {
                $item_meta[] = $item->getAuthorHTMLLink();
            } elseif (!$item->Feed->User->isAnonymous()) {
                $item_user = $item->Feed->User;
            }
            $item_meta[] = sprintf($this->_('Feed: %s'), $this->_application->LinkTo(h($item->Feed->title), $this->getUrl($item->feed_id)));
            $data['content'][] = array(
                'title' => $item->title,
                'body' => $item->body,
                'url' => $this->getUrl($item->feed_id . '/' . $item->id),
                'timestamp' => $item->published,
                'user' => $item_user,
                'meta_html' => $item_meta,
            );
        }
        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => array('base' => '/aggregator'),
                    'title' => sprintf($this->_('Show all (%s)'), $model->Item->criteria()->hidden_is(0)->count())
                )
            );
        }

        return $data;
    }

    /* End implementation of Plugg_Widgets_Widget */

    /* Start implementation of Plugg_User_Widget */

    public function userWidgetGetNames()
    {
        return array(
            'new_items' => Plugg_User_Plugin::WIDGET_TYPE_PUBLIC | Plugg_User_Plugin::WIDGET_TYPE_CACHEABLE
        );
    }

    public function userWidgetGetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'new_items':
                return $this->_('Feed articles');
        }
    }

    public function userWidgetGetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'new_items':
                return $this->_('Displays rencent feed articles published by the user.');
        }
    }

    public function userWidgetGetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'new_items':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of recent feed items to display'),
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
            case 'new_items':
                return $this->_renderNewItemsUserWidget($widgetSettings, $identity);
        }
    }

    private function _renderNewItemsUserWidget($widgetSettings, $identity)
    {
        $id = $identity->id;
        $model = $this->getModel();
        $feeds = $model->Feed
            ->criteria()
            ->status_is(self::FEED_STATUS_APPROVED)
            ->fetchByUser($identity->id);
        if ($feeds->count() <= 0) return;

        $count = $model->Item
            ->criteria()
            ->feedId_in($feeds->getAllIds())
            ->count();
        if ($count <= 0) return;

        $items = $model->Item
            ->criteria()
            ->feedId_in($feeds->getAllIds())
            ->fetch($widgetSettings['limit'], 0, 'published', 'DESC')
            ->with('Feed');
        $data = array();
        foreach ($items as $item) {
            $item_user = null;
            $item_meta = array();
            if ($item->author && $item->Feed->author_pref == self::FEED_AUTHOR_PREF_ENTRY_AUTHOR) {
                $item_meta[] = $item->getAuthorHTMLLink();
            }
            $item_meta[] = sprintf($this->_('Feed: %s'), $this->_application->LinkTo(h($item->Feed->title), $this->getUrl($item->feed_id)));
            $data[] = array(
                'title' => $item->title,
                'body' => $item->body,
                'url' => array('base' => '/aggregator', 'path' => $item->feed_id . '/' . $item->id),
                'timestamp' => $item->published,
                'meta_html' => $item_meta,
            );
        }

        return array(
            'content' => $data,
            'links' => array()
        );
    }

    /* End implementation of Plugg_User_Widget */

    public function getHTMLPurifier($feed)
    {
        // Define allowed HTML tags
        $html_tags_allowed = array('a', 'abbr', 'acronym', 'b', 'blockquote', 'br', 'caption',
            'cite', 'code', 'dd', 'del', 'dfn', 'div', 'dl', 'dt', 'em', 'h2', 'h3', 'h4', 'h5',
            'i', 'ins', 'kbd', 'li', 'ol', 'p', 'pre', 's', 'small', 'strike', 'strong', 'sub', 'sup',
            'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'tt', 'u', 'ul','var'
        );
        if ($feed->allow_image) $html_tags_allowed[] = 'img';

        // Get service locator
        $locator = $this->_application->getLocator();

        // Override default HTMLPurifier config options
        $config_options = array_merge($locator->getDefaultParam('HTMLPurifierConfig', 'options'), array(
            'URI.Host' => $feed->host,
            'URI.DisableExternalResources' => $feed->allow_external_resources == 0,
            'AutoFormat.Linkify' => false,
            'AutoFormat.AutoParagraph' => false,
            'HTML.AllowedElements' => $html_tags_allowed
        ));

        $service_id = $this->name . '-feed-' . $feed->id;
        return $locator->getService('HTMLPurifier', $service_id, array(
            'HTMLPurifierConfig' => $locator->getService('HTMLPurifierConfig', $service_id, array(
                'options' => $config_options
            ))
        ));
    }
    
    public function loadFeedItems($feed, $numberOfItems = 10, $timeout = 15)
    {
        if (false === $f_items = $this->_fetchFeedItems($feed, $numberOfItems, $timeout)) {
            return false;
        }

        $htmlpurifier = $this->getHTMLPurifier($feed);

        $last_publish = $feed->last_publish;
        foreach ($f_items as $f_item) {
            if ($feed->last_fetch > $timestamp = $f_item->get_date('U')) {
                continue;
            }
            if ($timestamp > $last_publish) {
                $last_publish = $timestamp;
            }

            $item = $feed->createItem();
            $item->md5 = $f_item->get_id(true);
            $item->title = $f_item->get_title();
            $item->url = $f_item->get_permalink();
            $item->body = $htmlpurifier->purify($f_item->get_content());
            if ($author = $f_item->get_author()) {
                $item->author = $author->get_name();
                $item->author_link = $author->get_link();
            }
            $item->published = $timestamp;
            $categories = array();
            if ($f_categories = $f_item->get_categories()) {
                foreach ($f_categories as $category) {
                    $categories[] = $category->get_label();
                }
            }
            $item->categories = serialize($categories);
            $item->markNew();
        }

        $feed->last_fetch = time();
        $feed->last_publish = $last_publish;

        return $this->getModel()->commit();
    }
    
    public function loadFeedInfo($feed, $timeout = 15)
    {
        // Make sure valid URL is set
        if (!$feed->site_url) {
            throw new Plugg_Aggregator_Exception_InvalidSiteUrl('Invalid site URL.');
        }

        if (!$feed->host) {
            if (!$host = @parse_url($feed->site_url, PHP_URL_HOST)) {
                throw new Plugg_Aggregator_Exception_InvalidSiteUrl('Invalid site URL.');
            }

            // Convert host if any regex defined
            require_once dirname(__FILE__) . '/hosts.php';
            if ($host_replaced = preg_replace($host_matches, $host_replacements, $host)) {
                $feed->host = $host_replaced;
            } else {
                $feed->host = $host;
            }
        }

        // Fetch feed
        if (!$sp_feed = $this->_getSimplePie($feed, $timeout)) {
            throw new Plugg_Aggregator_Exception_FeedNotFound('Feed not found.');
        }

        // Load feed meta data
        if (!$feed->title) $feed->title = $sp_feed->get_title();
        if (!$feed->feed_url) {
            if (!$feed->feed_url = $sp_feed->get_link()) {
                throw new Plugg_Aggregator_Exception_InvalidFeedUrl('Invalid feed URL.');
            }
        }
        if (!$feed->favicon_url) $feed->favicon_url = $sp_feed->get_favicon();
        if (!$feed->description) $feed->description = $sp_feed->get_description();
        $feed->language = $sp_feed->get_language();
    }

    private function _getSimplePie($feed, $timeout)
    {
        require_once dirname(__FILE__) . '/lib/simplepie_1.2/simplepie.inc';
        $simple_pie = new SimplePie();
        $simple_pie->set_cache_location(dirname(__FILE__) . '/cache');
        $simple_pie->set_feed_url($feed->feed_url ? $feed->feed_url : $feed->site_url);
        $simple_pie->set_stupidly_fast(true); // disable sanitization
        $simple_pie->enable_order_by_date(true);
        $simple_pie->set_timeout($timeout);
        $simple_pie->set_output_encoding(SABAI_CHARSET);
        if ($feed->last_fetch) {
            $simple_pie->set_cache_duration(18000); // allow cache up to 5 hours
        } else {
            $simple_pie->set_cache_duration(0); // force not to use cached files if last fetch time is empty
        }
        if (!$simple_pie->init()) return false;

        return $simple_pie;
    }

    private function _fetchFeedItems($feed, $numberOfItems, $timeout)
    {
        // Fetch feed
        if (!$sp_feed = $this->_getSimplePie($feed, $timeout)) {
            return false;
        }

        $sp_feed->handle_content_type();

        return $sp_feed->get_items(0, $numberOfItems);
    }

    public function sendFeedApprovedEmail($feed)
    {
        if (!$this->getConfig('approvedNotifyEmail', 'enable')) return;
        
        // No need to send if feed owner is anonymous
        if ($feed->User->isAnonymous()) {
            return;
        }

        $tags = $this->_getEmailTags($feed);
        return $this->_application->getPlugin('Mail')->getSender()
            ->mailSend(
                array($feed->User->email, $feed->User->username),
                strtr($this->getConfig('approvedNotifyEmail', 'subject'), $tags),
                strtr($this->getConfig('approvedNotifyEmail', 'body'), $tags)
            );
    }

    public function sendFeedAddedEmail($feed)
    {
        if (!$this->getConfig('addedNotifyEmail', 'enable')) return;
        
        // No need to send if feed owner is anonymous
        if ($feed->User->isAnonymous()) return;

        $tags = $this->_getEmailTags($feed);
        
        return $this->_application->getPlugin('Mail')->getSender()
            ->mailSend(
                array($feed->User->email, $feed->User->username),
                strtr($this->getConfig('addedNotifyEmail', 'subject'), $tags),
                strtr($this->getConfig('addedNotifyEmail', 'body'), $tags)
            );
    }

    private function _getEmailTags($feed)
    {
        return array(
            '{SITE_NAME}' => $site_name = $this->_application->SiteName(),
            '{SITE_URL}' => $this->_application->SiteUrl(),
            '{USER_NAME}' => $feed->User->username,
            '{USER_EMAIL}'=> $feed->User->email,
            '{FEED_SITE_URL}' => $feed->site_url,
            '{FEED_FEED_URL}' => $feed->feed_url,
            '{FEED_TITLE}' => $feed->title,
            '{FEED_MAIN_URL}' => $this->_application->createUrl(array(
                'base' => '/aggregator',
                'path' => $feed->id,
                'separator' => '&'
            )),
            '{FEED_USER_URL}' => $this->_application->User_IdentityUrl($feed->User, $this->name . '/' . $feed->id),
            '{FEEDS_USER_URL}' => $this->_application->User_IdentityUrl($feed->User, $this->name . '/feeds'),
            '{FEED_PING_URL}' => $this->_application->createUrl(array(
                'base' => '/aggregator',
                'path' => 'ping/' . $feed->id,
                'separator' => '&'
            )),
        );
    }
    
    public function getDefaultConfig()
    {
        return array(
            'approvedNotifyEmail' => array(
                'enable' => true,
                'subject' => $this->_('{SITE_NAME}: Notification of the approval of your site feed'),
                'body' => implode("\n\n", array(
                    $this->_('Hello {USER_NAME},'),
                    $this->_('The following feed of your website has been approved and now registered at {SITE_NAME}:'),
                    "{FEED_TITLE}\n{FEED_FEED_URL}",
                    $this->_('Your feed contents will soon be listed at the following locations:'),
                    "{FEED_MAIN_URL}\n{FEED_USER_URL}",
                    $this->_('You can also send update pings to the following URL to notify updates:'),
                    "{FEED_PING_URL}",
                    $this->_('If you need to modify your feed data, go to the following link:'),
                    "{FEEDS_USER_URL}",
                    "-----------\n{SITE_NAME}\n{SITE_URL}"
                ))
            ),
            'addedNotifyEmail' => array(
                'enable' => true,
                'subject' => $this->_('{SITE_NAME}: Notification of the registration of your site feed'),
                'body' => implode("\n\n", array(
                    $this->_('Hello {USER_NAME},'),
                    $this->_('The following feed of your website has been registered at {SITE_NAME} by the site administrator:'),
                    "{FEED_TITLE}\n{FEED_FEED_URL}",
                    $this->_('Your feed contents will soon be listed at the following locations:'),
                    "{FEED_MAIN_URL}\n{FEED_USER_URL}",
                    $this->_('You can also send update pings to the following URL to notify updates:'),
                    "{FEED_PING_URL}",
                    $this->_('If you need to modify your feed data, go to the following link:'),
                    "{FEEDS_USER_URL}",
                    "-----------\n{SITE_NAME}\n{SITE_URL}"
                ))
            ),
            'default' => array(
                'allowImage' => false,
                'allowExternalResources' => false,
                'authorPref' => Plugg_Aggregator_Plugin::FEED_AUTHOR_PREF_ENTRY_AUTHOR,
            ),
            'cronIntervalDays' => 7,
            'feedsRequireApproval' => true,
        );
    }
}
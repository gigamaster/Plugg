<?php
class Plugg_Forum_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_System_Routable_Admin, Plugg_Widgets_Widget, Plugg_AdminWidget_Widget,
    Plugg_User_Permissionable, Plugg_User_Widget, Plugg_Groups_Widget, Plugg_Search_Searchable
{
    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/forum' => array(
                'controller' => 'Index',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/forum/starred' => array(
                'controller' => 'StarredTopics',
                'title' => $this->_('Starred'),
                'type' => Plugg::ROUTE_TAB,
                'access_callback' => true,
            ),
            '/forum/my_topics' => array(
                'controller' => 'MyTopics',
                'title' => $this->_('My topics'),
                'type' => Plugg::ROUTE_TAB,
                'access_callback' => true,
            ),
            '/groups/:group_name/forum' => array(
                'controller' => 'Group_Index',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/groups/:group_name/forum/starred' => array(
                'controller' => 'Group_StarredTopics',
                'title' => $this->_('Starred'),
                'type' => Plugg::ROUTE_TAB,
                'access_callback' => true,
            ),
            '/groups/:group_name/forum/my_topics' => array(
                'controller' => 'Group_MyTopics',
                'title' => $this->_('My topics'),
                'type' => Plugg::ROUTE_TAB,
                'access_callback' => true,
            ),
            '/groups/:group_name/forum/new_topic' => array(
                'controller' => 'Group_NewTopic',
                'title' => $this->_('Add new topic'),
                'type' => Plugg::ROUTE_MENU,
                'weight' => -1,
            ),
            '/groups/:group_name/forum/:topic_id' => array(
                'controller' => 'Group_Topic',
                'format' => array(':topic_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/groups/:group_name/forum/:topic_id/content' => array(
                'controller' => 'Group_Topic_Content',
            ),
            '/groups/:group_name/forum/:topic_id/comments' => array(
                'controller' => 'Group_Topic_Comments',
                'title' => $this->_('Listing comments')
            ),
            '/groups/:group_name/forum/:topic_id/add_comment' => array(
                'controller' => 'Group_Topic_AddComment',
                'ajax' => true,
                'title' => $this->_('Submit a comment'),
            ),
            '/groups/:group_name/forum/:topic_id/:comment_id' => array(
                'controller' => 'Group_Topic_Comment',
                'format' => array(':comment_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/groups/:group_name/forum/:topic_id/:comment_id/content' => array(
                'controller' => 'Group_Topic_Comment_Content',
            ),
            '/groups/:group_name/forum/:topic_id/:comment_id/reply' => array(
                'controller' => 'Group_Topic_Comment_Reply',
                'title' => $this->_('Reply'),
            ),
            '/groups/:group_name/forum/:topic_id/:comment_id/edit' => array(
                'controller' => 'Group_Topic_Comment_Edit',
                'title' => $this->_('Edit'),
            ),
            '/groups/:group_name/forum/:topic_id/:comment_id/delete' => array(
                'controller' => 'Group_Topic_Comment_Delete',
                'title' => $this->_('Delete'),
            ),
            '/groups/:group_name/forum/:topic_id/rss' => array(
                'controller' => 'Group_Topic_Rss',
                'title' => $this->_('RSS'),
                'type' => Plugg::ROUTE_MENU,
            ),
            '/groups/:group_name/forum/:topic_id/edit' => array(
                'controller' => 'Group_Topic_Edit',
                'title' => $this->_('Edit'),
            ),
            '/groups/:group_name/forum/:topic_id/delete' => array(
                'controller' => 'Group_Topic_Delete',
                'title' => $this->_('Delete'),
            ),
            '/groups/:group_name/forum/rss' => array(
                'controller' => 'Group_RSS',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('RSS'),
            ),
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/forum':
            case '/groups/:group_name/forum':
                if (($user_id = $request->asInt('user_id'))
                    && ($user_identity = $this->_application->User_Identity($user_id))
                    && !$user_identity->isAnonymous()
                ) {
                    $this->_application->user = $user_identity;
                }
                return true;
                
            case '/forum/starred':
            case '/forum/my_topics':
            case '/groups/:group_name/forum/starred':
            case '/groups/:group_name/forum/my_topics':
                return $this->_application->getUser()->isAuthenticated();
                
            case '/groups/:group_name/forum/:topic_id':
                // Make sure the requested topic exists and it belongs to the requested group
                return ($this->_application->topic = $this->getRequestedEntity($request, 'Topic', 'topic_id'))
                    && $this->_application->topic->group_id == $this->_application->group->id;
                    
            case '/groups/:group_name/forum/:topic_id/:comment_id':
                // Make sure the requested comment exists and it belongs to the requested topic
                return ($this->_application->comment = $this->getRequestedEntity($request, 'Comment', 'comment_id'))
                    && $this->_application->comment->topic_id == $this->_application->topic->id;
        }
    }

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/forum':
            case '/groups/:group_name/forum':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Topics') : $title;
                
            case '/groups/:group_name/forum/:topic_id':
                return $this->_application->topic->title;
        }
    }

    /* End implementation of Plugg_System_Routable_Main */

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/content/forum' => array(
                'controller' => 'Index',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
                'title_callback' => true,
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path){}

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/content/forum':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Topics') : $title;
        }
    }

    /* End implementation of Plugg_System_Routable_Admin */
    
    /* Start implementation of Plugg_User_Permissionable */

    public function userPermissionableGetPermissions()
    {
        return array(
            'forum topic post' => $this->_('Create topics'),
            'forum topic edit own' => $this->_('Edit own topics'),
            'forum topic edit any' => $this->_("Edit other user's topics"),
            'forum topic delete own' => $this->_('Delete own topics'),
            'forum topic delete any' => $this->_("Delete other user's topics"),
            'forum topic close own' => $this->_('Close own topics'),
            'forum topic close any' => $this->_("Close other user's topics"),
            'forum topic sticky' => $this->_('Make topics sticky/unsticky'),
            'forum comment' => $this->_('Post comments'),
            'forum comment edit own' => $this->_('Edit own comments'),
            'forum comment edit any' => $this->_("Edit other user's comments"),
            'forum comment delete own' => $this->_('Delete own comments'),
            'forum comment delete any' => $this->_("Delete other user's comments"),
            'forum attach file' => $this->_('Attach files to topics and comments'),
        );
    }
    
    public function userPermissionableGetDefaultPermissions()
    {
        return array('forum topic post', 'forum topic edit own', 'forum topic close own', 'forum comment', 'forum comment edit own', 'forum comment delete own');
    }
    
    /* End implementation of Plugg_User_Permissionable */

    /* Start implementation of Plugg_Widgets_Widget interface */

    public function widgetsGetWidgetNames()
    {
        return array(
            'topics' => Plugg_Widgets_Plugin::WIDGET_TYPE_CACHEABLE,
            'latest' => Plugg_Widgets_Plugin::WIDGET_TYPE_CACHEABLE,
        );
    }

    public function widgetsGetWidgetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_('Forum topics');
            case 'latest':
                return $this->_('Lastest forum discussions');
        }
    }

    public function widgetsGetWidgetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_('Displays a list of forum topics.');
            case 'latest':
                return $this->_('Displays lastest discussions in the forum.');
        }
    }

    public function widgetsGetWidgetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_getTopicsWidgetSettings($currentValues);
            case 'latest':
                return $this->_getLatestWidgetSettings($currentValues);
        }
    }

    public function widgetsGetWidgetContent($widgetName, $widgetSettings, Sabai_User $user)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_renderTopicsWidget($widgetSettings);
            case 'latest':
                return $this->_renderLatestWidget($widgetSettings);
        }
    }

    private function _renderTopicsWidget($settings)
    {
        $data = array();
        switch ($settings['order']) {
            case 'newest':
                $sort = 'created';
                $order = 'DESC';
                break;
            case 'oldest':
                $sort = 'created';
                $order = 'ASC';
                break;
            default:
                $sort = 'last_posted';
                $order = 'DESC';
        }
        $topics = $this->getModel()->Topic
            ->fetch($settings['limit'], 0, $sort, $order)
            ->with('User')
            ->with('Group');
        foreach ($topics as $entry) {
            $data['content'][] = array(
                'title' => $entry->getTitle(),
                'url' => $entry->Group->getUrl('forum/' . $entry->id),
                'timestamp_label' => $sort === 'last_posted' ? sprintf('Last update') : null,
                'timestamp' => $entry->$sort,
                'meta_html' => array(
                    sprintf('Topic by %s', $this->_application->User_IdentityLink($entry->User)),
                    sprintf('<a href="%s">%s</a>', $entry->Group->getUrl(), h($entry->Group->display_name)),
                    sprintf($this->_n('1 comment', '%d comments', $entry->comment_count), $entry->comment_count),
                ),
                'body' => $entry->body_html,
                'thumbnail' => $entry->User->image_thumbnail,
                'thumbnail_link' => $this->_application->User_IdentityUrl($entry->User),
                'thumbnail_title' => $entry->User->display_name,
            );
        }
        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => $this->_application->Url('/forum'),
                    'title' => sprintf($this->_('Show all (%d)'), $this->getModel()->Topic->count()) 
                )
            );
        }
        $data['menu'] = $this->_getTopicsWidgetMenu($settings['order']);

        return $data;
    }
    
    private function _renderLatestWidget($settings)
    {
        $data = array();
        $topics = $this->getModel()->Topic
            ->fetch($settings['limit'], 0, 'last_posted', 'DESC')
            ->with('User')
            ->with('Group')
            ->with('LastComment', 'User');
        foreach ($topics as $entry) {
            if ($comment = $entry->LastComment) {
                // Create title if none set for the comment
                $data['content'][] = array(
                    'title' => $comment->title ? $comment->title : sprintf('Re: %s', $entry->title),
                    'url' => $entry->Group->getUrl('forum/' . $entry->id . '/' . $comment->id),
                    'timestamp' => $comment->created,
                    'user' => $comment->User,
                    'meta_html' => array(
                        sprintf('Topic: <a href="%s" title="%s">%s</a>', $entry->Group->getUrl('forum/' . $entry->id), h($entry->title), h($entry->getTitle(30))),
                        sprintf('<a href="%s">%s</a>', $entry->Group->getUrl(), h($entry->Group->display_name)),
                    ),
                    'body' => $comment->body_html,
                    'thumbnail' => $comment->User->image_thumbnail,
                    'thumbnail_link' => $this->_application->User_IdentityUrl($comment->User),
                    'thumbnail_title' => $comment->User->display_name,
                );
            } else {
                $data['content'][] = array(
                    'title' => $entry->getTitle(),
                    'url' => $entry->Group->getUrl('forum/' . $entry->id),
                    'timestamp' => $entry->created,
                    'user' => $entry->User,
                    'meta_html' => array(
                        sprintf('<a href="%s">%s</a>', $entry->Group->getUrl(), h($entry->Group->display_name)),
                    ),
                    'body' => $entry->body_html,
                    'thumbnail' => $entry->User->image_thumbnail,
                    'thumbnail_link' => $this->_application->User_IdentityUrl($entry->User),
                    'thumbnail_title' => $entry->User->display_name,
                );
            }
        }
        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => $this->_application->Url('/forum'),
                    'title' => sprintf($this->_('Show all (%d)'), $this->getModel()->Topic->count()) 
                )
            );
        }

        return $data;
    }

    /* End implementation of Plugg_Widgets_Widget interface */
    
    /* Start implementation of Plugg_AdminWidget_Widget interface */

    public function adminWidgetGetNames()
    {
        return array(
            'topics' => Plugg_AdminWidget_Plugin::WIDGET_TYPE_CACHEABLE,
            'latest' => Plugg_AdminWidget_Plugin::WIDGET_TYPE_CACHEABLE,
        );
    }

    public function adminWidgetGetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_('Forum topics');
            case 'latest':
                return $this->_('Latest forum discussions');
        }
    }

    public function adminWidgetGetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_('Displays a list of forum topics.');
            case 'latest':
                return $this->_('Displays lastest discussions in the forum.');
        }
    }

    public function adminWidgetGetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_getTopicsWidgetSettings($currentValues);
            case 'latest':
                return $this->_getLatestWidgetSettings($currentValues);
        }
    }

    public function adminWidgetGetContent($widgetName, $widgetSettings)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_renderTopicsAdminWidget($widgetSettings);
            case 'latest':
                return $this->_renderLatestAdminWidget($widgetSettings);
        }
    }

    private function _renderTopicsAdminWidget($settings)
    {
        $data = array();
        switch ($settings['order']) {
            case 'newest':
                $sort = 'created';
                $order = 'DESC';
                break;
            case 'oldest':
                $sort = 'created';
                $order = 'ASC';
                break;
            default:
                $sort = 'last_posted';
                $order = 'DESC';
        }
        $topics = $this->getModel()->Topic
            ->fetch($settings['limit'], 0, $sort, $order)
            ->with('User')
            ->with('Group');
        foreach ($topics as $entry) {
            $data['content'][] = array(
                'title' => $entry->getTitle(),
                'url' => $entry->Group->getUrl('forum/' . $entry->id),
                'timestamp_label' => $sort === 'last_posted' ? sprintf('Last update') : null,
                'timestamp' => $entry->$sort,
                'meta_html' => array(
                    sprintf('Topic by %s', $this->_application->User_IdentityLink($entry->User)),
                    sprintf('<a href="%s">%s</a>', $entry->Group->getUrl(), h($entry->Group->display_name)),
                    sprintf($this->_n('1 comment', '%d comments', $entry->comment_count), $entry->comment_count),
                ),
                'body' => $entry->body_html,
                'thumbnail' => $entry->User->image_thumbnail,
                'thumbnail_link' => $this->_application->User_IdentityUrl($entry->User),
                'thumbnail_title' => $entry->User->display_name,
            );
        }
        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => $this->_application->Url('/content/forum'),
                    'title' => sprintf($this->_('Show all (%d)'), $this->getModel()->Topic->count()) 
                )
            );
        }
        $data['menu'] = $this->_getTopicsWidgetMenu($settings['order']);

        return $data;
    }
    
    private function _renderLatestAdminWidget($settings)
    {
        $data = array();
        $topics = $this->getModel()->Topic
            ->fetch($settings['limit'], 0, 'last_posted', 'DESC')
            ->with('User')
            ->with('Group')
            ->with('LastComment', 'User');
        foreach ($topics as $entry) {
            if ($comment = $entry->LastComment) {
                // Create title if none set for the comment
                $data['content'][] = array(
                    'title' => $comment->title ? $comment->title : sprintf('Re: %s', $entry->title),
                    'url' => $entry->Group->getUrl('forum/' . $entry->id . '/' . $comment->id),
                    'timestamp' => $comment->created,
                    'user' => $comment->User,
                    'meta_html' => array(
                        sprintf('Topic: <a href="%s" title="%s">%s</a>', $entry->Group->getUrl('forum/' . $entry->id), h($entry->title), h($entry->getTitle(30))),
                        sprintf('<a href="%s">%s</a>', $entry->Group->getUrl(), h($entry->Group->display_name)),
                    ),
                    'body' => $comment->body_html,
                    'thumbnail' => $comment->User->image_thumbnail,
                    'thumbnail_link' => $this->_application->User_IdentityUrl($comment->User),
                    'thumbnail_title' => $comment->User->display_name,
                );
            } else {
                $data['content'][] = array(
                    'title' => $entry->getTitle(),
                    'url' => $entry->Group->getUrl('forum/' . $entry->id),
                    'timestamp' => $entry->created,
                    'user' => $entry->User,
                    'meta_html' => array(
                        sprintf('<a href="%s">%s</a>', $entry->Group->getUrl(), h($entry->Group->display_name)),
                    ),
                    'body' => $entry->body_html,
                    'thumbnail' => $entry->User->image_thumbnail,
                    'thumbnail_link' => $this->_application->User_IdentityUrl($entry->User),
                    'thumbnail_title' => $entry->User->display_name,
                );
            }
        }
        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => $this->_application->Url('/content/forum'),
                    'title' => sprintf($this->_('Show all (%d)'), $this->getModel()->Topic->count()) 
                )
            );
        }

        return $data;
    }

    /* End implementation of Plugg_AdminWidget_Widget interface */

    /* Start implementation of Plugg_User_Widget interface */

    public function userWidgetGetNames()
    {
        return array(
            'topics' => Plugg_User_Plugin::WIDGET_TYPE_CACHEABLE,
        );
    }

    public function userWidgetGetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_('Forum topics');
        }
    }

    public function userWidgetGetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_('Displays a list of forum topics.');
        }
    }

    public function userWidgetGetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_getTopicsWidgetSettings($currentValues);
        }
    }

    public function userWidgetGetContent($widgetName, $widgetSettings, Sabai_User_Identity $identity, $isOwner, $isAdmin)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_renderTopicsUserWidget($widgetSettings, $identity);
        }
    }

    private function _renderTopicsUserWidget($settings, $identity)
    {
        $data = array();
        switch ($settings['order']) {
            case 'newest':
                $sort = 'created';
                $order = 'DESC';
                break;
            case 'oldest':
                $sort = 'created';
                $order = 'ASC';
                break;
            default:
                $sort = 'last_posted';
                $order = 'DESC';
        }
        $topics = $this->getModel()->Topic
            ->fetchByUser($identity->id, $settings['limit'], 0, $sort, $order)
            ->with('Group');
        foreach ($topics as $entry) {
            $data['content'][] = array(
                'title' => $entry->getTitle(),
                'url' => $entry->Group->getUrl('forum/' . $entry->id),
                'timestamp_label' => $sort === 'last_posted' ? sprintf('Last update') : null,
                'timestamp' => $entry->$sort,
                'meta_html' => array(
                    sprintf('<a href="%s">%s</a>', $entry->Group->getUrl(), h($entry->Group->display_name)),
                    sprintf($this->_n('1 comment', '%d comments', $entry->comment_count), $entry->comment_count),
                ),
                'body' => $entry->body_html
            );
        }
        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => $this->_application->getUrl('/forum', array('user_id' => $identity->id)),
                    'title' => sprintf($this->_('Show all (%d)'), $this->getModel()->Topic->countByUser($identity->id))
                )
            );
        }
        $data['menu'] = $this->_getTopicsWidgetMenu($settings['order']);

        return $data;
    }

    /* End implementation of Plugg_User_Widget interface */

    /* Start implementation of Plugg_Groups_Widget */

    public function groupsWidgetGetNames()
    {
        return array(
            'topics' => Plugg_Groups_Plugin::WIDGET_TYPE_CACHEABLE,
            'latest' => Plugg_Groups_Plugin::WIDGET_TYPE_CACHEABLE
        );
    }

    public function groupsWidgetGetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_('Forum topics');
            case 'latest':
                return $this->_('Lastest forum discussions');
        }
    }

    public function groupsWidgetGetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_('Displays recent forum topics in the group.');
            case 'latest':
                return $this->_('Displays lastest discussions in the forum.');
        }
    }

    public function groupsWidgetGetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_getTopicsWidgetSettings($currentValues);
            case 'latest':
                return $this->_getLatestWidgetSettings($currentValues);
        }
    }

    public function groupsWidgetGetContent($widgetName, $widgetSettings, Plugg_Groups_Model_Group $group, $isMemberViewing, $isAdminViewing)
    {
        switch ($widgetName) {
            case 'topics':
                return $this->_renderTopicsGroupsWidget($widgetSettings, $group);
            case 'latest':
                return $this->_renderLatestGroupsWidget($widgetSettings, $group);
        }
    }

    private function _renderTopicsGroupsWidget($settings, $group)
    {
        $data = array();
        switch ($settings['order']) {
            case 'newest':
                $sort = 'created';
                $order = 'DESC';
                break;
            case 'oldest':
                $sort = 'created';
                $order = 'ASC';
                break;
            default:
                $sort = 'last_posted';
                $order = 'DESC';
        }
        $topics = $this->getModel()->Topic
            ->criteria()
            ->fetchByGroup($group->id, $settings['limit'], 0, $sort, $order)
            ->with('User');
        foreach ($topics as $entry) {
            $data['content'][] = array(
                'title' => $entry->getTitle(),
                'url' => $group->getUrl('forum/' . $entry->id),
                'timestamp_label' => $sort === 'last_posted' ? sprintf('Last update') : null,
                'timestamp' => $entry->$sort,
                'meta_html' => array(
                    sprintf('Topic by %s', $this->_application->User_IdentityLink($entry->User)),
                    sprintf($this->_n('1 comment', '%d comments', $entry->comment_count), $entry->comment_count),
                ),
                'body' => $entry->body_html,
                'thumbnail' => $entry->User->image_thumbnail,
                'thumbnail_link' => $this->_application->User_IdentityUrl($entry->User),
                'thumbnail_title' => $entry->User->display_name,
            );
        }
        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => $group->getUrl('forum'),
                    'title' => sprintf($this->_('Show all (%d)'), $this->getModel()->Topic->countByGroup($group->id))
                )
            );
        }
        $data['menu'] = $this->_getTopicsWidgetMenu($settings['order']);

        return $data;
    }
    
    private function _renderLatestGroupsWidget($settings, $group)
    {
        $data = array();
        $topics = $this->getModel()->Topic
            ->fetchByGroup($group->id, $settings['limit'], 0, 'last_posted', 'DESC')
            ->with('User')
            ->with('LastComment', 'User');
        foreach ($topics as $entry) {
            if ($comment = $entry->LastComment) {
                $data['content'][] = array(
                    'title' => $comment->title ? $comment->title : sprintf('Re: %s', $entry->title),
                    'url' => $entry->Group->getUrl('forum/' . $entry->id . '/' . $comment->id),
                    'timestamp' => $comment->created,
                    'user' => $comment->User,
                    'meta_html' => array(
                        sprintf('Topic: <a href="%s" title="%s">%s</a>', $entry->Group->getUrl('forum/' . $entry->id), h($entry->title), h($entry->getTitle(40))),
                    ),
                    'body' => $comment->body_html,
                    'thumbnail' => $comment->User->image_thumbnail,
                    'thumbnail_link' => $this->_application->User_IdentityUrl($comment->User),
                    'thumbnail_title' => $comment->User->display_name,
                );
            } else {
                $data['content'][] = array(
                    'title' => $entry->getTitle(),
                    'url' => $entry->Group->getUrl('forum/' . $entry->id),
                    'timestamp' => $entry->created,
                    'user' => $entry->User,
                    'body' => $entry->body_html,
                    'thumbnail' => $entry->User->image_thumbnail,
                    'thumbnail_link' => $this->_application->User_IdentityUrl($entry->User),
                    'thumbnail_title' => $entry->User->display_name,
                );
            }
        }
        if (!empty($data['content'])) {
            $data['links'] = array(
                array(
                    'url' => $group->getUrl('forum'),
                    'title' => sprintf($this->_('Show all (%d)'), $this->getModel()->Topic->countByGroup($group->id))
                )
            );
        }

        return $data;
    }

    /* End implementation of Plugg_Groups_Widget */
    
    /* Start implementation of Plugg_Search_Searchable */
    
    public function searchGetNames()
    {
        return array('topics', 'comments');
    }

    public function searchGetNicename($searchName)
    {
        switch ($searchName) {
            case 'topics': return $this->_('%s - Topics');
            case 'comments': return $this->_('%s - Comments');
        }
    }

    public function searchGetContentUrl($searchName, $contentId)
    {
        switch ($searchName) {
            case 'topics':
                if ($topic = $this->getModel()->Topic->fetchById($contentId)) {
                    return $this->_application->createUrl(array(
                        'base' => '/groups',
                        'path' => $topic->Group->name . '/forum/' . $contentId,
                    ));
                }
                
            case 'comments':
                if ($comment = $this->getModel()->Comment->fetchById($contentId)) {
                    return $this->_application->createUrl(array(
                        'base' => '/groups',
                        'path' => $comment->Topic->Group->name . '/forum/' . $comment->topic_id . '/' . $contentId,
                        'fragment' => 'plugg-forum-comment' . $contentId
                    ));
                }
        }
    }

    public function searchFetchContents($searchName, $limit, $offset)
    {
        $contents = array();
        switch ($searchName) {
            case 'topics':
                $topics = $this->getModel()->Topic->fetch($limit, $offset, 'created', 'DESC');
                foreach ($topics as $topic) {
                    $contents[] = $this->_topicToContent($topic);
                }
                break;
                
            case 'comments':
                $comments = $this->getModel()->Comment->fetch($limit, $offset, 'created', 'DESC');
                foreach ($comments->with('Topic') as $comment) {
                    $contents[] = $this->_commentToContent($comment);
                }
                break;
        }
        
        return new ArrayObject($contents);
    }

    public function searchCountContents($searchName)
    {
        switch ($searchName) {
            case 'topics':
                return $this->getModel()->Topic->count();
                    
            case 'comments':
                return $this->getModel()->Comment->count();
        }
        
        return 0;
    }

    public function searchFetchContentsByIds($searchName, $contentIds)
    {
        $contents = array();
        switch ($searchName) {
            case 'topics':
                $topics = $this->getModel()->Topic
                    ->criteria()
                    ->id_in($contentIds)
                    ->fetch();
                foreach ($topics as $topic) {
                    $contents[] = $this->_topicToContent($topic);
                }
                break;
                
            case 'comments':
                $comments = $this->getModel()->Comment
                    ->criteria()
                    ->id_in($contentIds)
                    ->fetch();
                foreach ($comments->with('Topic') as $comment) {
                    $contents[] = $this->_commentToContent($comment);
                }
                break;
        }
        
        return new ArrayObject($contents);
    }
    
    public function searchFetchContentsSince($searchName, $timestamp, $limit, $offset)
    {
        $contents = array();
        switch ($searchName) {
            case 'topics':
                $topics = $this->getModel()->Topic->criteria()->created_isGreaterThan($timestamp)->fetch($limit, $offset, 'id', 'ASC');
                foreach ($topics as $topic) {
                    $contents[] = $this->_topicToContent($topic);
                }
                break;
                
            case 'comments':
                $comments = $this->getModel()->Comment->criteria()->created_isGreaterThan($timestamp)->fetch($limit, $offset, 'id', 'ASC');
                foreach ($comments->with('Topic') as $comment) {
                    $contents[] = $this->_commentToContent($comment);
                }
                break;
        }
        
        return new ArrayObject($contents);
    }

    public function searchCountContentsSince($searchName, $timestamp)
    {
        switch ($searchName) {
            case 'topics':
                return $this->getModel()->Topic->criteria()->created_isGreaterThan($timestamp)->count();
                
            case 'comments':
                return $this->getModel()->Comment->criteria()->created_isGreaterThan($timestamp)->count();
        }
    }

    private function _topicToContent($topic)
    {
        return array(
            'id' => $id = $topic->id,
            'user_id' => $topic->user_id,
            'title' => $topic->title,
            'body' => $topic->body_html,
            'created' => $topic->created,
            'modified' => $topic->updated,
            'keywords' => array(),
            'group' => sprintf('t:%d;', $id)
        );
    }

    private function _commentToContent($comment)
    {
        return array(
            'id' => $id = $comment->id,
            'user_id' => $comment->user_id,
            'title' => $comment->title ? $comment->title : sprintf($this->_('Re: %s'), $comment->Topic->title),
            'body' => $comment->body_html,
            'created' => $comment->created,
            'modified' => $comment->updated,
            'keywords' => array(),
            'group' => sprintf('t:%d;c:%d', $comment->topic_id, $id)
        );
    }
    
    public function onUserIdentityDeleteSuccess($identity)
    {
        $model = $this->getModel();
        
        $stars = $model->Star->fetchByUser($identity->id);
        foreach ($stars as $star) $star->markRemoved();
        $views = $model->View->fetchByUser($identity->id);
        foreach ($views as $view) $view->markRemoved();

        $model->commit();
    }
    
    public function onGroupsGroupDeleteSuccess($group)
    {
        $model = $this->getModel();
        
        $topics = $model->Star->fetchByGroup($group->id);
        foreach ($topics as $topic) $topic->markRemoved();

        $model->commit();
    }
    
    public function onForumTopicDeleteSuccess($topic)
    {
        // Specify group so that any content related to this node will be purged
        $group = sprintf('t:%d;', $topic->id);

        // Purge conetnt from search engine
        $this->_application->getPlugin('Search')->purgeContentGroup('forum', $group);
    }

    public function onForumCommentDeleteSuccess($comment)
    {
        // Purge conetnt from search engine
        $this->_application->getPlugin('Search')->purgeContent('forum', 'comments', $comment->id);
    }
    
    private function _getTopicsWidgetMenu($current)
    {
        return array(
            array(
                'text' => $this->_('Active'),
                'settings' => array('order' => 'active'),
                'current' => $current === 'active'
            ),
            array(
                'text' => $this->_('Newest'),
                'settings' => array('order' => 'newest'),
                'current' => $current === 'newest'
            ),
            array(
                'text' => $this->_('Oldest'),
                'settings' => array('order' => 'oldest'),
                'current' => $current === 'oldest'
            ),
        );
    }
    
    private function _getTopicsWidgetSettings($currentValues)
    {
        return array(
            'limit' => array(
                '#type' => 'radios',
                '#title' => $this->_('Number of topics to display'),
                '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10, 15 => 15, 20 => 20, 30 => 30),
                '#delimiter' => '&nbsp;',
                '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 10,
            ),
            'order' => array(
                '#type' => 'radios',
                '#title' => $this->_('Default topic display order'),
                '#options' => array(
                    'active' => $this->_('Active first'),
                    'newest' => $this->_('Newest first'),
                    'oldest' => $this->_('Oldest first')
                ),
                '#delimiter' => '&nbsp;',
                '#default_value' => isset($currentValues['order']) ? $currentValues['order'] : 'active',
            ),
        );
    }
    
    private function _getLatestWidgetSettings($currentValues)
    {
        return array(
            'limit' => array(
                '#type' => 'radios',
                '#title' => $this->_('Number of topics to display'),
                '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10, 15 => 15, 20 => 20, 30 => 30),
                '#delimiter' => '&nbsp;',
                '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 10,
            ),
        );
    }
}
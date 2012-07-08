<?php
class Plugg_Groups_Controller_Main_Group extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // View
        $vars = array('widgets' => $this->_getGroupWidgets($request));
        
        $response->setContent($this->RenderTemplate('groups_main_group', $vars));
    }

    private function _getGroupWidgets(Sabai_Request $request)
    {
        $ret = array(
            Plugg_Groups_Plugin::WIDGET_POSITION_TOP => array(),
            Plugg_Groups_Plugin::WIDGET_POSITION_BOTTOM => array(),
            Plugg_Groups_Plugin::WIDGET_POSITION_LEFT => array(),
            Plugg_Groups_Plugin::WIDGET_POSITION_RIGHT => array(),
        );

        $need_commit = false;
        $is_member = $this->membership && $this->membership->isActive();
        $is_admin = $this->getUser()->isSuperUser()
            || ($this->membership && $this->membership->isAdmin());

        $widgets = $this->_getWidgetsByGroup($request);
        if ($widgets->count() == 0) {
            // No user widget data exists, so fetch the default widget settings and create copies for the user
            $widgets = $this->_getWidgets($request);
            if ($widgets->count() > 0) {
                $widgets = $this->_createGroupWidgets($request, $widgets);
                $need_commit = true;
            }
        }

        foreach ($widgets->with('Widget') as $widget) {
            if (!$widget->Widget
                || (!$plugin = $this->getPlugin($widget->Widget->plugin)) // not a valid plugin widget
            ) continue;

            if (!$widget->Widget->isCacheable() // content of this widget may not be cached
                || $is_member
                || $is_admin
                || !$widget->cache_lifetime
                || !$widget->cache // no cache
                || $widget->cache_time + $widget->cache_lifetime < time() // cache expired
                || (false === $widget_content = unserialize($widget->cache)) // failed unserializing cached content
            ) {
                // Build widget content
                if (false === $widget_content = $this->_buildWidgetContent($request, $widget, $plugin, $is_member, $is_admin)) {
                    continue;
                }

                if ($widget->cache_lifetime) {
                    // Cache content
                    $widget->cache = serialize($widget_content);
                    $widget->cache_time = time();

                    $need_commit = true;
                }
            }

            $ret[$widget->position][] = array(
                'title' => $widget->title,
                'content' => isset($widget_content['content']) ? $widget_content['content'] : array(),
                'menu' => (array)@$widget_content['menu'],
                'links' => (array)@$widget_content['links'],
                'name' => $widget->Widget->name,
                'plugin' => $plugin->name,
                'path' => '/groups/' . $this->group->name . '/widget/' . $plugin->name . '/' . $widget->Widget->name,
            );
        }

        // Commit widgets
        if ($need_commit) $this->getPluginModel()->commit();

        return $ret;
    }

    private function _getWidgetsByGroup($request)
    {
        return $this->getPluginModel()->Activewidget->criteria()
            ->groupId_is($this->group->id)
            ->fetch(0, 0, 'order', 'ASC');
    }

    private function _getWidgets($request)
    {
        return $this->getPluginModel()->Activewidget->criteria()
            ->groupId_is(0)
            ->fetch(0, 0, 'order', 'ASC');
    }

    private function _createGroupWidgets($request, $widgets)
    {
        $new_widgets = array();
        $model = $this->getPluginModel();
        foreach ($widgets as $widget) {
            $new_widget = $model->create('Activewidget');
            foreach (array('title', 'order', 'position', 'settings', 'widget_id', 'cache_lifetime') as $key) {
                $new_widget->$key = $widget->$key;
            }
            $new_widget->assignGroup($this->group);
            $new_widget->markNew();
            $new_widgets[] = $new_widget;
        }

        return $model->Activewidget->createCollection($new_widgets);
    }

    private function _buildWidgetContent($request, $widget, $plugin, $isMember, $isAdmin)
    {
        // Render widget content
        return $plugin->groupsWidgetGetContent(
            $widget->Widget->name,
            unserialize($widget->settings),
            $this->group,
            $isMember,
            $isAdmin
        );
    }
}
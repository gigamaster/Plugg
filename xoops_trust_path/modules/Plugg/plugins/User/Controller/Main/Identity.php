<?php
class Plugg_User_Controller_Main_Identity extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Is user profile allowed to be viewed by any user?
        if (!$this->getPlugin()->getConfig('allowViewAnyUser')) {
            if (!$this->getUser()->isAuthenticated()) {
                $response->setLoginRequiredError();
                return;
            } else {
                // Check permission if viewing other user's profile
                if ($this->identity->id != $this->getUser()->id) {
                    if (!$this->getUser()->hasPermission('user profile view any')) {
                        $response->setError($this->_('Permission denied'));
                        return;
                    }
                }
            }
        }

        // View
        $model = $this->getPluginModel();
        
        $vars = array(
            'stat' => $model->Stat->fetchByUser($this->identity->id)->getFirst(),
            'status' => $model->Status->fetchByUser($this->identity->id)->getFirst(),
            'profile_html' => $this->getPlugin()->getManagerPlugin()->userViewRenderIdentity(
                $this->identity, $this->_getUserFields($request)
            ),
            'widgets' => $this->_getUserWidgets($request),
            'show_edit_image_link' => ($this->getPlugin()->getManagerPlugin() instanceof Plugg_User_Manager_ApplicationWithImage)
                && ($this->User()->hasPermission('user image edit any')
                    || ($this->identity_is_me && $this->User()->hasPermission('user image edit own')))
        );

        $response->setContent($this->RenderTemplate('user_main_identity', $vars));

        // Dispatch event
        $this->DispatchEvent('UserIdentityViewed', array($request, $this->identity));
    }

    private function _getUserFields(Sabai_Request $request)
    {
        $ret = $relationships = array();
        $fields = $this->getPlugin()->getFields();
        if (!empty($fields[0])
            && $this->identity->hasData('user_fields')
            && ($user_fields = $this->identity->getData('user_fields'))
        ) { 
            if (!$this->identity->hasData('user_fields_visibility')
                || (!$user_fields_visibility = $this->identity->getData('user_fields_visibility'))
            ) {
                $user_fields_visibility = array();
            }
            if ($this->getUser()->isAuthenticated()) {
                $relationships = $this->getPlugin('Friends')->getRelationships($this->identity, $this->getUser());
            }
            foreach ($fields[0] as $fieldset) {
                if (!empty($fields[$fieldset->id])) {
                    foreach ($fields[$fieldset->id] as $field) {
                        if (!isset($user_fields[$field->name])) continue;
                        
                        $field_value = $user_fields[$field->name];
                        if ($field->visibility_control && $this->getUser()->id != $this->identity->id) {
                            $field_visibility = @$user_fields_visibility[$field->name];
                            if (empty($field_visibility) // nothing set for visibility
                                || in_array('@private', $field_visibility) // private?
                                || (in_array('@user', $field_visibility) && !$this->getUser()->isAuthenticated()) // only registered users?
                                || (!in_array('@all', $field_visibility) && !array_intersect($field_visibility, $relationships)) // only specific relationships
                            ) {
                                continue;
                            }
                        }
                        $field_type = $field->FormField->type;
                        $field_settings = array();
                        foreach (unserialize($field->settings) as $field_setting_key => $field_setting) {
                            $field_settings['#' . $field_setting_key] = $field_setting;
                        }
                        $ret[$fieldset->id]['fields'][$field->id] = array(
                            'name' => $field->name,
                            'title' => $field->title,
                            'content' => $field_value,
                            'html' => $this->getPlugin('Form')->getElementHandler($field_type)->formFieldRenderHtml($field_type, $field_value, $field_settings),
                        );
                    }
                    if (!empty($ret[$fieldset->id])) {
                        $ret[$fieldset->id]['name'] = $fieldset->name;
                        $ret[$fieldset->id]['title'] = $fieldset->title;
                    }
                }
            }
        }
        
        return $ret;
    }

    private function _getUserWidgets(Sabai_Request $request)
    {
        $ret = array(
            Plugg_User_Plugin::WIDGET_POSITION_TOP => array(),
            Plugg_User_Plugin::WIDGET_POSITION_LEFT => array(),
            Plugg_User_Plugin::WIDGET_POSITION_RIGHT => array(),
            Plugg_User_Plugin::WIDGET_POSITION_BOTTOM => array()
        );

        $need_commit = false;
        $is_owner = $this->getUser()->id == $this->identity->id;
        $is_admin = $this->getUser()->isSuperUser();

        // Is the user allowed to create custom widget layout?
        if ($this->User_IdentityPermissions($this->identity, array('user widget edit own', 'user widget edit any'))) {
            $widgets = $this->_getWidgetsByUser($this->identity->id);
            if ($widgets->count() == 0) {
                // No user widget data exists, so fetch the default widget settings and create copies for the user
                $widgets = $this->_getWidgetsByUser();
                if ($widgets->count() > 0) {
                    $widgets = $this->_createUserWidgets($widgets);
                    $need_commit = true;
                }
            }
        } else {
            $widgets = $this->_getWidgetsByUser();
        }

        foreach ($widgets->with('Widget') as $widget) {
            if (!$widget->Widget
                || (!$plugin = $this->getPlugin($widget->Widget->plugin)) // not a valid plugin widget
            ) continue;
            if (!$widget->Widget->isCacheable() // content of this widget may not be cached
                || $is_owner
                || $is_admin
                || !$widget->cache_lifetime
                || !$widget->cache // no cache
                || $widget->cache_time + $widget->cache_lifetime < time() // cache expired
                || (false === $widget_content = unserialize($widget->cache)) // failed unserializing cached content
            ) {
                // Build widget content
                if (false === $widget_content = $this->_buildWidgetContent($widget, $plugin, $is_owner, $is_admin)) {
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
                'id' => $widget->id,
                'title' => $widget->title,
                'content' => isset($widget_content['content']) ? $widget_content['content'] : array(),
                'menu' => (array)@$widget_content['menu'],
                'links' => (array)@$widget_content['links'],
                'name' => $widget->Widget->name,
                'plugin' => $plugin->name,
                'path' => '/user/' . $this->identity->id . '/widget/' . $plugin->name . '/' . $widget->Widget->name
            );
        }

        // Commit widgets
        if ($need_commit) $this->getPluginModel()->commit();

        return $ret;
    }

    private function _getWidgetsByUser($userId = 0)
    {
        $model = $this->getPluginModel();
        $criteria = $model->createCriteria('Activewidget')->userId_is($userId);
        // Check if viewing other user's profile and if so is allowed viewing private tab contents
        if ($this->getUser()->id != $this->identity->id &&
            !$this->getUser()->hasPermission('user widget view any private')
        ) {
            $criteria->private_is(0); // not allowed to view other user's private tab contents
        }

        // Fetch widgets
        return $model->Activewidget->fetchByCriteria($criteria, 0, 0, 'order', 'ASC');
    }

    private function _createUserWidgets($widgets)
    {
        $new_widgets = array();
        $model = $this->getPluginModel();
        foreach ($widgets as $widget) {
            $new_widget = $model->create('Activewidget');
            foreach (array('title', 'order', 'position', 'settings', 'widget_id', 'private', 'cache_lifetime') as $key) {
                $new_widget->$key = $widget->$key;
            }
            $new_widget->assignUser($this->identity);
            $new_widget->markNew();
            $new_widgets[] = $new_widget;
        }

        return $model->Activewidget->createCollection($new_widgets);
    }

    private function _buildWidgetContent($widget, $plugin, $isOwner, $isAdmin)
    {
        // Render widget content
        return $plugin->userWidgetGetContent(
            $widget->Widget->name,
            unserialize($widget->settings),
            $this->identity,
            $isOwner,
            $isAdmin
        );
    }
}
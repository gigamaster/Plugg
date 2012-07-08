<?php
class Plugg_User_Controller_Main_Identity_Widget extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!$active_widget = $this->_getActiveWidget($request)) {
            $response->setError($this->_('Invalid request'));

            return;
        }

        // Override current settings if any requested
        $settings = array();
        $current_settings = unserialize($active_widget->settings);
        $widget_settings = $this->widget_plugin->userWidgetGetSettings($this->widget->name);
        foreach (array_keys($widget_settings) as $setting_name) {
            if ($request->has($setting_name)) {
                $setting_requested = $request->get($setting_name);
                // If options are set for the setting, make sure the requested value is one of the option values
                if ($options = @$widget_settings[$setting_name]['#options']) {
                    foreach ((array)$setting_requested as $_setting_requested) {
                        if (!in_array($_setting_requested, array_keys($options))) {
                            $setting_requested = @$current_settings[$setting_name];
                            break;
                        }
                    }
                }
                $settings[$setting_name] = $setting_requested;
            } else {
                $settings[$setting_name] = @$current_settings[$setting_name];
            }
        }

        $is_owner = $this->getUser()->id == $this->identity->id;
        $is_admin = $this->getUser()->isSuperUser();

        // Build content only when the requested setting is different from the default setting and no valid cache exists
        if (!$this->widget->isCacheable() // make sure widget content may be cached
            || $is_owner
            || $is_admin
            || $settings != $current_settings
            || !$active_widget->cache // cache does not exist
            || $active_widget->cache_time + $active_widget->cache_lifetime < time() // cache expired
            || (false === $widget_content = unserialize($active_widget->cache)) // failed unserializing cached content
        ) {
            // Build widget content
            if (false === $widget_content = $this->widget_plugin->userWidgetGetContent(
                $this->widget->name,
                $settings,
                $this->identity,
                $is_owner,
                $is_admin
            )) {
                $response->setError();
                return;
            }
        }

        // Assign view data
        $vars = array(
            'widget' => array(
                'id' => $active_widget->id,
                'title' => $active_widget->title,
                'content' => isset($widget_content['content']) ? $widget_content['content'] : array(),
                'menu' => (array)@$widget_content['menu'],
                'links' => (array)@$widget_content['links'],
                'name' => $this->widget->name,
                'plugin' => $this->widget_plugin->name,
                'path' => '/user/' . $this->identity->id . '/widget/' . $this->widget_plugin->name . '/' . $this->widget->name,
            ),
        );
        
        $response->setContent($this->RenderTemplate('user_main_identity_widget', $vars))
            ->setPageInfo($active_widget->title);
    }

    private function _getActiveWidget($request)
    {
        $model = $this->getPluginModel();
        $active_widget = $model->Activewidget
            ->criteria()
            ->widgetId_is($this->widget->id)
            ->userId_is($this->identity->id)
            ->fetch()
            ->getFirst();

        if ($active_widget) return $active_widget;

        // No active widget yet for this user, so fetch the default active widget
        $default_widget = $model->Activewidget
            ->criteria()
            ->widgetId_is($this->widget->id)
            ->userId_is(0)
            ->fetch()
            ->getFirst();

        if (!$default_widget) return false; // default active widget does not exist

        // Make sure the default active widget does not have any cache
        $default_widget->cache = '';

        return $default_widget;
    }
}
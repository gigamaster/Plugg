<?php
class Plugg_AdminWidget_Controller_Admin_ViewWidget extends Sabai_Application_Controller
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
        $widget_settings = $this->widget_plugin->adminWidgetGetSettings($this->widget->name);
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

        // Build content only when the requested setting is different from the default setting and no valid cache exists
        if (!$this->widget->isCacheable() // make sure widget content may be cached
            || $settings != $current_settings
            || !$active_widget->cache // cache does not exist
            || $active_widget->cache_time + $active_widget->cache_lifetime < time() // cache expired
            || (false === $widget_content = unserialize($active_widget->cache)) // failed unserializing cached content
        ) {
            // Build widget content
            if (false === $widget_content = $this->widget_plugin->adminWidgetGetContent(
                $this->widget->name,
                $settings
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
                'path' => '/adminwidget/' . $this->widget_plugin->name . '/' . $this->widget->name,
            ),
        );

        $response->setContent($this->RenderTemplate('adminwidget_admin_viewwidget', $vars))
            ->setPageInfo($active_widget->title);
    }

    private function _getActiveWidget($request)
    {
        return $this->getPluginModel()->Activewidget
            ->criteria()
            ->widgetId_is($this->widget->id)
            ->fetch()
            ->getFirst();
    }
}
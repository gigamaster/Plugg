<?php
class Plugg_AdminWidget_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Admin
{
    const WIDGET_POSITION_TOP = 0, WIDGET_POSITION_LEFT = 1, WIDGET_POSITION_RIGHT = 2, WIDGET_POSITION_BOTTOM = 3;

    const WIDGET_TYPE_CACHEABLE = 1;

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/adminwidget' => array(
                'controller' => 'Index',
                'type' => Plugg::ROUTE_TAB,
            ),
            '/adminwidget/:plugin_name/:widget_name' => array(
                'format' => array(
                    ':plugin_name' => '\w+',
                    ':widget_name' => '\w+',
                ),
                'controller' => 'ViewWidget',
                'access_callback' => true
            ),
            '/adminwidget/edit_layout' => array(
                'controller' => 'EditLayout',
                'type' => Plugg::ROUTE_MENU,
                'ajax' => false,
                'title' => $this->_('Customize dashboard')
            ),
            '/adminwidget/submit' => array(
                'controller' => 'Submit',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/adminwidget/:widget_id' => array(
                'format' => array(
                    ':widget_id' => '\d+',
                ),
                'access_callback' => true,
            ),
            '/adminwidget/:widget_id/edit' => array(
                'controller' => 'EditWidget',
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/adminwidget/:plugin_name/:widget_name':
                if ((!$plugin_name = $request->asStr('plugin_name'))
                    || (!$widget_name = $request->asStr('widget_name'))
                    || (!$plugin = $this->_application->getPlugin($plugin_name))
                ) {
                    return false;
                }

                $widget = $this->getModel()->Widget->criteria()->plugin_is($plugin_name)
                    ->name_is($widget_name)->fetch()->getFirst();

                if (!$widget) return false;

                $this->_application->widget = $widget;
                $this->_application->widget_plugin = $plugin;

                return true;
            case '/adminwidget/:widget_id':
                return ($this->_application->widget = $this->getRequestedEntity($request, 'Widget', 'widget_id')) ? true : false;
        }
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType){}

    /* End implementation of Plugg_System_Routable_Admin */


    public function getWidgetData()
    {
        // Fetch available widgets and data
        $widgets = array();
        foreach ($this->getModel()->Widget->fetch(0, 0, 'plugin', 'ASC') as $widget) {
            // skip if plugin of the widget is not enabled
            if (!$widget_plugin = $this->_application->getPlugin($widget->plugin)) continue;

            $widgets[$widget->id] = array(
                'id' => $widget->id,
                'name' => $widget->name,
                'title' => $widget_plugin->adminWidgetGetTitle($widget->name),
                'summary' => $widget_plugin->adminWidgetGetSummary($widget->name),
                'settings' => $widget_plugin->adminWidgetGetSettings($widget->name),
                'plugin' => $widget_plugin->nicename,
            );
        }

        return $widgets;
    }

    public function onAdminWidgetWidgetInstalled($pluginEntity, $plugin)
    {
        if ($widgets = $plugin->adminWidgetGetNames()) {
            $model = $this->getModel();
            $this->_createPluginWidgets($model->create('Widget'), $pluginEntity->name, $widgets);
            $model->commit();
        }
    }

    public function onAdminWidgetWidgetUninstalled($pluginEntity, $plugin)
    {
        $this->_deletePluginWidgets($pluginEntity->name);
    }

    public function onAdminWidgetWidgetUpgraded($pluginEntity, $plugin)
    {
        if (!$widgets = $plugin->adminWidgetGetNames()) {
            $this->_deletePluginWidgets($pluginEntity->name);
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
            $this->_createPluginWidgets(
                $model->create('Widget'),
                $pluginEntity->name,
                array_diff($widgets, $widgets_already_installed)
            );
            $model->commit();
        }
    }

    private function _createPluginWidgets($prototype, $pluginName, $widgets)
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
            $active_widget->title = $widget_plugin->adminWidgetGetTitle($widget_name);
            $settings = array();
            if ($widget_settings = $widget_plugin->adminWidgetGetSettings($widget_name)) {
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

    private function _deletePluginWidgets($pluginName)
    {
        $model = $this->getModel();
        foreach ($model->Widget->criteria()->plugin_is($pluginName)->fetch() as $widget) {
            $widget->markRemoved();
        }

        return $model->commit();
    }
}
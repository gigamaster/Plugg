<?php
class Plugg_Widgets_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_System_Routable_Admin
{
    const WIDGET_POSITION_TOP = 0, WIDGET_POSITION_LEFT = 1, WIDGET_POSITION_RIGHT = 2, WIDGET_POSITION_BOTTOM = 3;

    const WIDGET_TYPE_CACHEABLE = 1, WIDGET_TYPE_NONCACHEABLE = 2, WIDGET_TYPE_REQUIRE_AUTHENTICATED = 4,
        WIDGET_TYPE_REQUIRE_ANONYMOUS = 8;

    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/widgets' => array(
                'controller' => 'Index',
                'title' => $this->_nicename,
            ),
            '/widgets/:plugin_name/:widget_name' => array(
                'format' => array(
                    ':plugin_name' => '\w+',
                    ':widget_name' => '\w+',
                ),
                'controller' => 'ViewWidget',
                'access_callback' => true
            )
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/widgets/:plugin_name/:widget_name':
                if ((!$plugin_name = $request->asStr('plugin_name'))
                    || (!$widget_name = $request->asStr('widget_name'))
                    || (!$plugin = $this->_application->getPlugin($plugin_name))
                ) {
                    return false;
                }

                $widget = $this->getModel()->Widget
                    ->criteria()
                    ->plugin_is($plugin_name)
                    ->name_is($widget_name)
                    ->fetch()
                    ->getFirst();

                if (!$widget || !$widget->canViewContent($this->_application->getUser())) return false;

                $this->_application->widget = $widget;
                $this->_application->widget_plugin = $plugin;

                return true;
        }
    }

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType){}

    /* End implementation of Plugg_System_Routable_Main */

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/system/widgets' => array(
                'controller' => 'System_Index',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_nicename,
            ),
            '/system/widgets/submit' => array(
                'controller' => 'System_Submit',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/system/widgets/:widget_id' => array(
                'format' => array(
                    ':widget_id' => '\d+',
                ),
                'access_callback' => true,
            ),
            '/system/widgets/:widget_id/edit' => array(
                'controller' => 'System_EditWidget',
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/system/widgets/:widget_id':
                return ($this->_application->widget = $this->getRequestedEntity($request, 'Widget', 'widget_id')) ? true : false;
        }
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType){}

    /* End implementation of Plugg_System_Routable_Admin */

    public function isValidWidgetRequest($request)
    {
        if ((!$plugin_name = $request->asStr('plugin_name'))
            || (!$widget_name = $request->asStr('widget_name'))
            || (!$plugin = $this->_application->getPlugin($plugin_name))
        ) {
            return false;
        }

        $widget = $this->getModel()->Widget->criteria()->plugin_is($plugin_name)
            ->name_is($widget_name)->fetch()->getFirst();

        if (!$widget || !$widget->canViewContent($this->_application->getUser())) return false;

        $this->_application->widget = $widget;
        $this->_application->widget_plugin = $plugin;

        return true;
    }

    public function onWidgetsWidgetInstalled($pluginEntity, $plugin)
    {
        if ($widgets = $plugin->widgetsGetWidgetNames()) {
            $this->_createPluginWidgets($pluginEntity->name, $widgets);
        }
    }

    public function onWidgetsWidgetUninstalled($pluginEntity, $plugin)
    {
        $this->_deletePluginWidgets($pluginEntity->name);
    }

    public function onWidgetsWidgetUpgraded($pluginEntity, $plugin)
    {
        if (!$widgets = $plugin->widgetsGetWidgetNames()) {
            $this->_deletePluginWidgets($pluginEntity->name);
        } else {
            $widgets_already_installed = array();
            foreach ($this->getModel()->Widget->criteria()->plugin_is($pluginEntity->name)->fetch() as $current_widget) {
                if (isset($widgets[$current_widget->name])) {
                    $widgets_already_installed[$current_widget->name] = $current_widget->name;
                    if ($widgets[$current_widget->name] != $current_widget->type) {
                        $current_widget->type = $widgets[$current_widget->name];
                    }
                } else {
                    $current_widget->markRemoved();
                }
            }
            $this->_createPluginWidgets(
                $pluginEntity->name,
                array_diff_key($widgets, $widgets_already_installed)
            );
        }
    }

    private function _createPluginWidgets($pluginName, $widgets)
    {
        $model = $this->getModel();
        foreach ($widgets as $widget_name => $widget_type) {
            if (empty($widget_name)) continue;
            $widget = $model->create('Widget');
            $widget->name = $widget_name;
            $widget->plugin = $pluginName;
            $widget->type = $widget_type;
            $widget->markNew();
            
            // Activate widget
            $widget_plugin = $this->_application->getPlugin($pluginName);
            $active_widget = $widget->createActivewidget();
            $active_widget->title = $widget_plugin->widgetsGetWidgetTitle($widget_name);
            $settings = array();
            if ($widget_settings = $widget_plugin->widgetsGetWidgetSettings($widget_name)) {
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
        
        return $model->commit();
    }

    private function _deletePluginWidgets($pluginName)
    {
        $model = $this->getModel();
        foreach ($model->Widget->criteria()->plugin_is($pluginName)->fetch() as $widget) {
            $widget->markRemoved();
        }

        return $model->commit();
    }

    function getWidgetData()
    {
        // Fetch available widgets and data
        $widgets = array();
        foreach ($this->getModel()->Widget->fetch(0, 0, 'plugin', 'ASC') as $widget) {
            // skip if plugin of the widget is not enabled
            if (!$widget_plugin = $this->_application->getPlugin($widget->plugin)) continue;

            $widgets[$widget->id] = array(
                'id' => $widget->id,
                'name' => $widget->name,
                'title' => $widget_plugin->widgetsGetWidgetTitle($widget->name),
                'summary' => $widget_plugin->widgetsGetWidgetSummary($widget->name),
                'settings' => $widget_plugin->widgetsGetWidgetSettings($widget->name),
                'plugin' => $widget_plugin->nicename,
            );
        }

        return $widgets;
    }

    public function getAdminWidgetsJS($formId)
    {
        return sprintf('
jQuery("#plugg .widgets-widgets").sortable({
    containment: ".widgets",
    items: ".widgets-widget",
    helper: "clone",
    revert: true,
    connectWith: ".widgets-widgets",
    opacity: 0.6,
    cursor: "move",
    placeholder: "widgets-widget-placeholder",
    forcePlaceholderSize: true,
    handle: "a.draggableHandle",
    stop: function(event, ui){
        var item_is_active = ui.item.isChildOf("td.widgets-active");
        var item_was_active = ui.item.hasClass("widgets-widget-active");
        if (!item_was_active && item_is_active) {
            var toggle_link = ui.item.find(".widgets-widget-control a.toggle");
            if (!toggle_link.is(":hidden")) {
                toggle_link.click();
            } else {
                ui.item.find(".widgets-widget-control a.toggleProcessed").click();
            }
        } else if (!item_is_active && !ui.item.find(".widgets-widget-form").is(":hidden")) {
            ui.item.find(".widgets-widget-control a.toggleProcessed").click();
        }
        if (item_is_active || item_was_active) {
            var $form = jQuery("#%s");
            jQuery.plugg.ajax({
                type: "post",
                url: $form.attr("action"),
                data: "%s=1&" + $form.serialize(),
                onSuccessRedirect: false,
                onErrorRedirect: false,
                onError: function(xhr, result, target) {alert("%s");},
            });
        }
        if (item_is_active) {
            ui.item.addClass("widgets-widget-active");
        } else {
            ui.item.removeClass("widgets-widget-active");
        }
    }
});
jQuery("#plugg .widgets-available .widgets-widgets").scrollFollow({
    speed: 1000,
    offset: 60,
});
', $formId, Plugg::PARAM_AJAX, $this->_('Failed updating widgets. Please reload the page and try again.'));
    }

    public function getAdminWidgetsCSS()
    {
        return '
#plugg .widgets {width:821px;}
#plugg .widgets,
#plugg .widgets table,
#plugg .widgets td {border:none; background:none;}
#plugg .widgets td {padding:0; vertical-align:top;}
#plugg .widgets table {border-collapse:separate; border-spacing:1px;}
#plugg .widgets td.widgets-active {background-color:#eee;}
#plugg .widgets-widgets {list-style:none; min-height:150px; min-width:250px; margin:0; padding:0 10px;}
#plugg .widgets-active-bottom {border-bottom-right-radius:5px; border-bottom-left-radius:5px; -webkit-border-bottom-right-radius:5px; -webkit-border-bottom-left-radius:5px; -moz-border-radius-bottomright:5px; -moz-border-radius-bottomleft:5px; -khtml-border-bottom-right-radius:5px; -khtml-border-bottom-left-radius:5px;}
#plugg .widgets-widget {list-style:none; margin:10px 0; background-color:#ddd; width:250px; padding:0; line-height:1.4em;}
#plugg .widgets-active-top .widgets-widget,
#plugg .widgets-active-bottom .widgets-widget {width:523px;}
#plugg .widgets-widget-details {font-size:0.9em; background-color:#fff; margin:0; padding:5px;}
#plugg .widgets-title {margin:0; margin-bottom:5px; text-align:center;}
#plugg .widgets-widget-placeholder {list-style:none; border:1px dashed #999; width:248px; line-height:1.4em; padding:0; margin:10px 0;}
#plugg .widgets-active-top .widgets-widget-placeholder,
#plugg .widgets-active-bottom .widgets-widget-placeholder {width:521px;}
#plugg .widgets-widget-control {padding:5px; font-size:12px; float:right; height:26px;}
#plugg .widgets-widget-title {padding:7px 9px; font-size:12px; line-height:1; font-weight:bold;}
#plugg .widgets-available {padding-left:20px; border:none;}
#plugg .widgets-available .widgets-widgets {min-height:300px; padding:0 10px; position:relative;}
#plugg .widgets-available .widgets-widget-control {display:none;}
#plugg .widgets-active-top,
#plugg .widgets-widget,
#plugg .widgets-widget-placeholder {border-top-right-radius:5px; border-top-left-radius:5px; -webkit-border-top-right-radius:5px; -webkit-border-top-left-radius:5px; -moz-border-radius-topright:5px; -moz-border-radius-topleft:5px; -khtml-border-top-right-radius:5px; -khtml-border-top-left-radius:5px;}
';
    }
}
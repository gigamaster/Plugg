<?php
class Plugg_User_Controller_Admin_Widgets_Submit extends Sabai_Application_Controller
{
    function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $url = array();

        if (!$request->isPost()) {
            $response->setError($this->_('Invalid request'), $url);
            return;
        }

        // Check token
        if (!$token_value = $request->asStr(Plugg::PARAM_TOKEN, false)) {
            $response->setError($this->_('Invalid request'), $url);
            return;
        }
        if (!Sabai_Token::validate($token_value, 'user_admin_widgets_submit', false)) {
            $response->setError($this->_('Invalid request'), $url);
            return;
        }

        // Fetch current widgets
        $current_widgets = array();
        foreach ($this->getPlugin()->getActiveWidgets() as $current_widget) {
            $current_widgets[$current_widget->widget_id] = $current_widget;
        }

        // Create panel widget records if any
        if ($widgets = $request->asArray('widgets')) {
            $widget_data = $this->getPlugin()->getWidgetData();
            $position = Plugg_User_Plugin::WIDGET_POSITION_LEFT;
            foreach ($widgets as $widget_order => $widget_id) {

                // Change position if widget order is not a numeric value
                if (!is_numeric($widget_order)) {
                    $position = constant('Plugg_User_Plugin::WIDGET_POSITION_' . $widget_order);
                    continue;
                }

                // Make sure that the widget exists
                if (!isset($widget_data[$widget_id])) continue;

                if (!isset($current_widgets[$widget_id])) {
                    $new_widget = $this->_createActiveWidget($request, $widget_id, $widget_data[$widget_id]);
                } else {
                    $new_widget = $current_widgets[$widget_id];
                    unset($current_widgets[$widget_id]);
                }

                // Update wiget data
                $new_widget->position = $position;
                $new_widget->order = $widget_order;
            }
        }

        // Remove current widgets that were not included in the request
        foreach ($current_widgets as $current_widget) {
            $current_widget->markRemoved();
        }

        // Commit all changes
        if (false === $this->getPluginModel()->commit()) {
            $response->setError($this->_('An error occurred while updating data.'), $url);
        } else {
            $response->setSuccess($this->_('Data updated successfully.'), $url);
        }
        if ($request->isAjax()) $response->setFlashEnabled(false);
    }

    private function _createActiveWidget($request, $widgetId, $widgetData)
    {
        $widget = $this->getPluginModel()->create('Activewidget');
        $widget->markNew();
        $widget->widget_id = $widgetId;
        $widget->title = $widgetData['title'];
        $widget->private = $widgetData['is_private'] ? 1 : 0;
        $settings = array();
        if (!empty($widgetData['settings'])) {
            foreach ($widgetData['settings'] as $k => $setting) {
                if (isset($setting['#default_value'])) {
                    $settings[$k] = $setting['#default_value'];
                }
            }
        }
        $widget->settings = serialize($settings);

        return $widget;
    }
}
<?php
class Plugg_AdminWidget_Controller_Admin_Submit extends Sabai_Application_Controller
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
        if (!Sabai_Token::validate($token_value, 'adminwidget_admin_widgets_submit', false)) {
            $response->setError($this->_('Invalid request'), $url);
            return;
        }

        // Fetch current widgets
        $model = $this->getPluginModel();
        $current_widgets = array();
        foreach ($model->Activewidget->fetch() as $current_widget) {
            $current_widgets[$current_widget->widget_id] = $current_widget;
        }

        // Create panel widget records if any
        if ($widgets = $request->asArray('widgets')) {
            $widget_data = $this->getPlugin()->getWidgetData();
            foreach ($widgets as $widget_order => $widget_id) {

                // Change position if widget order is not a numeric value
                if (!is_numeric($widget_order)) {
                    $position = constant('Plugg_AdminWidget_Plugin::WIDGET_POSITION_' . $widget_order);
                    continue;
                }

                // Make sure that the widget exists
                if (!isset($widget_data[$widget_id])) continue;

                if (!isset($current_widgets[$widget_id])) {
                    $new_widget = $model->create('Activewidget');
                    $new_widget->markNew();
                    $new_widget->widget_id = $widget_id;
                    $new_widget->title = $widget_data[$widget_id]['title'];
                    $settings = array();
                    if (!empty($widget_data[$widget_id]['settings'])) {
                        foreach ($widget_data[$widget_id]['settings'] as $k => $setting) {
                            if (isset($setting['#default_value'])) {
                                $settings[$k] = $setting['#default_value'];
                            }
                        }
                    }
                    $new_widget->settings = serialize($settings);
                } else {
                    $new_widget = $current_widgets[$widget_id];
                    unset($current_widgets[$widget_id]);
                }

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
}
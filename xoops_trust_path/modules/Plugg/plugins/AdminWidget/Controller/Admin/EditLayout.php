<?php
class Plugg_AdminWidget_Controller_Admin_EditLayout extends Sabai_Application_Controller
{
    function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $widgets = $this->getPlugin()->getWidgetData();
        $active_widgets = array(
            Plugg_AdminWidget_Plugin::WIDGET_POSITION_TOP => array(),
            Plugg_AdminWidget_Plugin::WIDGET_POSITION_BOTTOM => array(),
            Plugg_AdminWidget_Plugin::WIDGET_POSITION_LEFT => array(),
            Plugg_AdminWidget_Plugin::WIDGET_POSITION_RIGHT => array(),
        );
        $need_commit = false;
        foreach ($this->getPluginModel()->Activewidget->fetch(0, 0, 'order', 'ASC') as $active_widget) {
            $widget_id = $active_widget->widget_id;
            if ($widget = @$widgets[$widget_id]) {
                if (isset($active_widgets[$active_widget->position])) {
                    $active_widgets[$active_widget->position][$active_widget->id] = $widget;
                    unset($widgets[$widget_id]);
                } else {
                    // Invalid position set for this active widget.
                    // This happens when an active widget instance is saved without being positioned.
                    $active_widget->markRemoved();
                    $need_commit = true;
                }
            }
        }
        
        $vars = array(
            'widgets_top' => $active_widgets[Plugg_AdminWidget_Plugin::WIDGET_POSITION_TOP],
            'widgets_bottom' => $active_widgets[Plugg_AdminWidget_Plugin::WIDGET_POSITION_BOTTOM],
            'widgets_left' => $active_widgets[Plugg_AdminWidget_Plugin::WIDGET_POSITION_LEFT],
            'widgets_right' => $active_widgets[Plugg_AdminWidget_Plugin::WIDGET_POSITION_RIGHT],
            'widgets' => $widgets,
            'widgets_submit_path' => 'submit',
            'widgets_submit_token_id' => 'adminwidget_admin_widgets_submit',
            'widgets_edit_widget_path' => '%d/edit',
            'widgets_form_id' => 'plugg-adminwidget-admin-widgets-form',
        );
        $content = $this->RenderTemplate('adminwidget_admin_editlayout', $vars);
        
        $response->setContent($content)
            ->addJs($this->getPlugin('Widgets')->getAdminWidgetsJS('plugg-adminwidget-admin-widgets-form'))
            ->addCss($this->getPlugin('Widgets')->getAdminWidgetsCSS());

        if ($need_commit) $this->getPluginModel()->commit();
    }
}
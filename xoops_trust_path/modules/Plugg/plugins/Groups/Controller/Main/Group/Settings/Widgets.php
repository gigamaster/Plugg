<?php
class Plugg_Groups_Controller_Main_Group_Settings_Widgets extends Sabai_Application_Controller
{
    function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $widgets = $this->getPlugin()->getWidgetData();
        $active_widgets = array(
            Plugg_Groups_Plugin::WIDGET_POSITION_TOP => array(),
            Plugg_Groups_Plugin::WIDGET_POSITION_BOTTOM => array(),
            Plugg_Groups_Plugin::WIDGET_POSITION_LEFT => array(),
            Plugg_Groups_Plugin::WIDGET_POSITION_RIGHT => array(),
        );
        $need_commit = false;
        $current_widgets = $this->getPluginModel()->Activewidget->fetchByGroup($this->group->id, 0, 0, 'order', 'ASC');
        foreach ($current_widgets as $active_widget) {
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
            'widgets_top' => $active_widgets[Plugg_Groups_Plugin::WIDGET_POSITION_TOP],
            'widgets_bottom' => $active_widgets[Plugg_Groups_Plugin::WIDGET_POSITION_BOTTOM],
            'widgets_left' => $active_widgets[Plugg_Groups_Plugin::WIDGET_POSITION_LEFT],
            'widgets_right' => $active_widgets[Plugg_Groups_Plugin::WIDGET_POSITION_RIGHT],
            'widgets' => $widgets,
            'widgets_submit_path' => 'widgets/submit',
            'widgets_submit_token_id' => 'groups_main_group_manage_widgets_submit',
            'widgets_edit_widget_path' => 'widgets/%d/edit',
            'widgets_form_id' => 'plugg-groups-widgets-form',
        );
        $response->setContent($this->RenderTemplate('groups_admin_widgets', $vars))
            ->addJs($this->getPlugin('Widgets')->getAdminWidgetsJS('plugg-groups-widgets-form'))
            ->addCss($this->getPlugin('Widgets')->getAdminWidgetsCSS());

        if ($need_commit) $this->getPluginModel()->commit();
    }
}
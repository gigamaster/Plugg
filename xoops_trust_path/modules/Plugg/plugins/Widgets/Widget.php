<?php
interface Plugg_Widgets_Widget
{
    function widgetsGetWidgetNames();
    function widgetsGetWidgetTitle($widgetName);
    function widgetsGetWidgetSummary($widgetName);
    function widgetsGetWidgetSettings($widgetName, array $currentValues = array());
    function widgetsGetWidgetContent($widgetName, $widgetSettings, Sabai_User $user);
}
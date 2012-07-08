<?php
interface Plugg_AdminWidget_Widget
{
    function adminWidgetGetNames();
    function adminWidgetGetTitle($widgetName);
    function adminWidgetGetSummary($widgetName);
    function adminWidgetGetSettings($widgetName, array $currentValues = array());
    function adminWidgetGetContent($widgetName, $widgetSettings);
}
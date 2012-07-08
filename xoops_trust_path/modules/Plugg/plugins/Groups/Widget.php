<?php
interface Plugg_Groups_Widget
{
    function groupsWidgetGetNames();
    function groupsWidgetGetTitle($widgetName);
    function groupsWidgetGetSummary($widgetName);
    function groupsWidgetGetSettings($widgetName, array $currentValues = array());
    function groupsWidgetGetContent($widgetName, $widgetSettings, Plugg_Groups_Model_Group $group, $isMemberViewing, $isAdminViewing);
}
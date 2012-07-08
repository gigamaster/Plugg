<?php
interface Plugg_User_Widget
{
    function userWidgetGetNames();
    function userWidgetGetTitle($widgetName);
    function userWidgetGetSummary($widgetName);
    function userWidgetGetSettings($widgetName, array $currentValues = array());
    function userWidgetGetContent($widgetName, $widgetSettings, Sabai_User_Identity $identity, $isOwnerViewing, $isAdminViewing);
}
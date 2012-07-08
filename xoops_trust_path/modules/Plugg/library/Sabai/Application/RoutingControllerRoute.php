<?php
interface Sabai_Application_RoutingControllerRoute
{
    function __toString();
    function isForward();
    function getParams();
    function getController();
    function getControllerArgs();
    function getControllerFile();
}
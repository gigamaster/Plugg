<?php
interface Plugg_System_Cron
{
    function systemCronableGetNames();
    function systemCronableGetTitle($name);
    function systemCronableGetSummary($name);
    function systemCronableGetIntervals($name);
    function systemCronableRun($name, $log);
}
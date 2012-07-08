<?php
interface Plugg_System_Report
{
    function systemReportGetNames();
    function systemReportGetStatus($reportName);
}
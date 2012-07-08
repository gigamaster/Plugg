<?php
interface Plugg_HTMLPurifierFilter_CustomFilter
{
    function htmlpurifierfilterCustomFilterGetNames();
    function htmlpurifierfilterCustomFilterGetNicename($filterName);
    function htmlpurifierfilterCustomFilterGetSummary($filterName);
    function htmlpurifierfilterCustomFilterGetInstance($filterName);
    function htmlpurifierfilterCustomFilterGetTips($filterName);
}
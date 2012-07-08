<?php
interface Plugg_Filters_Filter
{
    function filtersFilterGetNames();
    function filtersFilterGetNicename($filterName);
    function filtersFilterGetSummary($filterName);
    function filtersFilterGetSettings($filterName);
    function filtersFilterGetTips($filterName, $long);
    function filtersFilterText($text, $filterName);
}
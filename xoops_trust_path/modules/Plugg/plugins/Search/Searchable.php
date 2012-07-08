<?php
interface Plugg_Search_Searchable
{
    function searchGetNames();
    function searchGetNicename($searchName);
    function searchGetContentUrl($searchName, $contentId);
    function searchFetchContents($searchName, $limit, $offset);
    function searchCountContents($searchName);
    function searchFetchContentsSince($searchName, $timestamp, $limit, $offset);
    function searchCountContentsSince($searchName, $timestamp);
    function searchFetchContentsByIds($searchName, $contentIds);
}
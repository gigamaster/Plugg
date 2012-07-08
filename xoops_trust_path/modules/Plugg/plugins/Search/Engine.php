<?php
interface Plugg_Search_Engine
{
    function searchEngineGetFeatures();
    function searchEngineGetSettings();
    function searchEngineFind($searchableIds, $keywords, $keywordsType, $keywordsNot, $limit, $offset, $order, $userId);
    function searchEngineCount($searchableIds, $keywords, $keywordsType, $keywordsNot, $userId);
    function searchEngineFindByPlugins($plugins, $keywords, $keywordsType, $keywordsNot, $limit, $offset, $order, $userId);
    function searchEngineCountByPlugins($plugins, $keywords, $keywordsType, $keywordsNot, $userId);
    function searchEngineListBySearchContentIds($searchableId, $contentIds, $order);
    function searchEnginePut($pluginName, $searchableId, $contentId, $title, $content, $userId, $ctime, $mtime, $keywords, $contentGroup);
    function searchEnginePurge($searchableId);
    function searchEnginePurgeContent($searchableId, $contentId);
    function searchEnginePurgeContentGroup($searchableId, $contentGroup);
    function searchEngineUpdateIndex();
}
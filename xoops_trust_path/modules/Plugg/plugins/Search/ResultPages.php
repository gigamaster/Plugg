<?php
class Plugg_Search_ResultPages extends Sabai_Page_Collection
{
    var $_engine;
    var $_searchableIds;
    var $_plugins;
    var $_keywords;
    var $_keywordsType;
    var $_keywordsNot;
    var $_order;
    var $_userId;

    function __construct($perpage, $engine, $keywords, $keywordsType, $keywordsNot, $order, $userId = null)
    {
        parent::__construct($perpage);
        $this->_engine = $engine;
        $this->_keywords = $keywords;
        $this->_keywordsType = $keywordsType;
        $this->_keywordsNot = $keywordsNot;
        $this->_order = $order;
        $this->_userId = $userId;
    }

    function setPlugins(array $plugins)
    {
        $this->_plugins = $plugins;
    }

    function getPlugins()
    {
        return $this->_plugins;
    }

    function setSearchables(array $searchableIds)
    {
        $this->_searchableIds = $searchableIds;
    }

    function getSearchables()
    {
        return $this->_searchableIds;
    }

    function _getElementCount()
    {
        if (!empty($this->_plugins) && $this->_isSearchByPluginsEnabled()) {
            return $this->_engine->searchEngineCountByPlugins($this->_plugins, $this->_keywords, $this->_keywordsType, $this->_keywordsNot, $this->_userId);
        } elseif (!empty($this->_searchableIds)) {
            return $this->_engine->searchEngineCount($this->_searchableIds, $this->_keywords, $this->_keywordsType, $this->_keywordsNot, $this->_userId);
        } else {
            return $this->_engine->searchEngineCount(array(), $this->_keywords, $this->_keywordsType, $this->_keywordsNot, $this->_userId);
        }
    }

    function _getElements($limit, $offset)
    {
        if (!empty($this->_plugins) && $this->_isSearchByPluginsEnabled()) {
            $result = $this->_engine->searchEngineFindByPlugins($this->_plugins, $this->_keywords, $this->_keywordsType, $this->_keywordsNot, $limit, $offset, $this->_order, $this->_userId);
        } elseif (!empty($this->_searchableIds)) {
            $result = $this->_engine->searchEngineFind($this->_searchableIds, $this->_keywords, $this->_keywordsType, $this->_keywordsNot, $limit, $offset, $this->_order, $this->_userId);
        } else {
            $result = $this->_engine->searchEngineFind(array(), $this->_keywords, $this->_keywordsType, $this->_keywordsNot, $limit, $offset, $this->_order, $this->_userId);
        }
        return new ArrayObject($result);
    }

    function _isSearchByPluginsEnabled()
    {
        return $this->_engine->searchEngineGetFeatures() & Plugg_Search_Plugin::ENGINE_FEATURE_FIND_BY_PLUGINS;
    }
}
<?php
class Plugg_Search_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_System_Routable_Admin
{
    const ENGINE_FEATURE_BOOLEAN_AND = 1, ENGINE_FEATURE_BOOLEAN_OR = 2, ENGINE_FEATURE_BOOLEAN_NOT = 4,
        ENGINE_FEATURE_BOOLEAN = 7,
        ENGINE_FEATURE_FIND_BY_SEARCHABLES = 8, ENGINE_FEATURE_FIND_BY_PLUGINS = 16,
        ENGINE_FEATURE_ORDER_BY_SCORE = 32, ENGINE_FEATURE_ORDER_BY_DATE_ASC = 64, ENGINE_FEATURE_ORDER_BY_DATE_DESC = 128,
        ENGINE_FEATURE_ORDER_BY_DATE = 192,
        SEARCHABLE_TYPE_PUBLIC = 1,
        ORDER_DATE_ASC = 1, ORDER_DATE_DESC = 2, ORDER_SCORE = 3,
        KEYWORDS_OR = 1, KEYWORDS_AND = 2;

    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/search' => array(
                'controller' => 'Index',
                'access_callback' => true,
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
            ),
            '/search/:searchable_id/:content_id' => array(
                'controller' => 'ViewContent',
                'format' => array(':searchable_id' => '\d+', ':content_id' => '\d+',),
            ),
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/search':
                // Make sure a valid search engine plugin is enabled.
                return $this->getEnginePlugin() ? true : false;
        }
    }

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType){}

    /* End implementation of Plugg_System_Routable_Main */

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/system/settings/search' => array(
                'controller' => 'System_Settings',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
            ),
            '/system/settings/search/:engine_id' => array(
                'controller' => 'System_Settings_Engine_Configure',
                'format' => array(':engine_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/system/settings/search/:engine_id/:searchable_id' => array(
                'controller' => 'System_Settings_Engine_ImportContent',
                'format' => array(':searchable_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/system/settings/search/:engine_id/:searchable_id':
                return (($this->_application->searchable = $this->getRequestedEntity($request, 'Searchable', 'searchable_id'))
                    && $this->_application->getPlugin($this->_application->searchable->plugin)) ? true : false;

            case '/system/settings/search/:engine_id':
                // Make sure a valid serach engine plugin is requested
                return ($this->_application->engine = $this->getRequestedEntity($request, 'Engine', 'engine_id'))
                    && ($plugin = $this->_application->getPlugin($this->_application->engine->plugin))
                    && $plugin instanceof Plugg_Search_Engine;
        }
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/system/settings/search/:engine_id/:searchable_id':
                $searchable_plugin = $this->_application->getPlugin($this->_application->searchable->plugin);
                return sprintf(
                    $searchable_plugin->searchGetNicename($this->_application->searchable->name),
                    $searchable_plugin->nicename
                );

            case '/system/settings/search/:engine_id':
                return $this->_application->getPlugin($this->_application->engine->plugin)->nicename;

        }
    }

    /* End implementation of Plugg_System_Routable_Admin */

    public function onPluggCron($lastrun, array $logs)
    {
        // Allow run this cron 1 time per day at most
        if (!empty($lastrun) && time() - $lastrun < 86400) return;
        $engine = $this->getEnginePlugin();
        foreach ($this->_getActiveSearchables() as $searchable) {
            // Make sure the searchable plugin is active
            if (!$plugin = $this->_application->getPlugin($searchable->plugin)) continue;
            
            $logs[] = sprintf(
                $this->_('Importing content of "%s" into the "%s" search engine.'),
                sprintf($plugin->searchGetNicename($searchable->name), $plugin->nicename),
                $engine->nicename // No search engine nice name?
            );

            $count_func = array($plugin, 'searchCountContentsSince');
            $fetch_func = array($plugin, 'searchFetchContentsSince');
            $pages = new Sabai_Page_Collection_Custom(
                $count_func, $fetch_func, 500, array(), array($searchable->name, $searchable->last_import)
            );
            foreach ($pages as $page) {
                foreach ($page->getElements() as $content) {
                    $engine->searchEnginePut(
                        $searchable->plugin,
                        $searchable->id,
                        $content['id'],
                        $content['title'],
                        $content['body'],
                        $content['user_id'],
                        $content['created'],
                        $content['modified'],
                        $content['keywords'],
                        $content['group']
                    );
                }
            }
            // Update last imported timestamp
            $searchable->last_import = time();
            $searchable->commit();
        }

        $engine->searchEngineUpdateIndex();
    }

    public function getEnginePlugin()
    {
        if (!$engine_name = $this->getConfig('searchEnginePlugin')) {
            throw new Plugg_Exception('Search engine plugin must be defined');
        }

        return $this->_application->getPlugin($engine_name);
    }
    
    public function onSearchEngineInstalled($pluginEntity, $plugin)
    {
        $model = $this->getModel();
        $engine = $model->create('Engine');
        $engine->plugin = $pluginEntity->name;
        $engine->markNew();
        
        foreach ($model->Searchable->fetch() as $searchable) {
            $engine->linkSearchable($searchable);
        }
        
        return $model->commit();
    }

    public function onSearchEngineUninstalled($pluginEntity, $plugin)
    {
        $engine = $this->getModel()->Engine->criteria()->plugin_is($pluginEntity->name)->fetch()->first();
        $engine->markRemoved();
        
        return $engine->commit();
    }

    public function onSearchSearchableInstalled($pluginEntity, $plugin)
    {
        if ($searches = $plugin->searchGetNames()) {
            $this->_createSearchables($pluginEntity->name, $searches); 
        }
    }

    public function onSearchSearchableUninstalled($pluginEntity, $plugin)
    {
        if ($deleted = $this->_deleteSearchables($pluginEntity->name)) {
            $this->_purge($deleted);
        }
    }

    public function onSearchSearchableUpgraded($pluginEntity, $plugin)
    {
        $deleted = array();

        // Update searches if any
        if (!$searches = $plugin->searchGetNames()) {
            $deleted = $this->_deleteSearchables($pluginEntity->name);
        } else {
            $model = $this->getModel();
            $searches_already_installed = array();
            foreach ($model->Searchable->criteria()->plugin_is($pluginEntity->name)->fetch() as $current_search) {
                if (in_array($current_search->name, $searches)) {
                    $searches_already_installed[] = $current_search->name;
                } else {
                    $current_search->markRemoved();
                    $deleted[] = $current_search->id;
                }
            }
            if (!$model->commit()) {
                return;
            }
            $this->_createSearchables($plugin_name, array_diff($searches, $searches_already_installed));
        }

        // Purge the search engine contents
        if (!empty($deleted)) {
            $this->_purge($deleted);
        }
    }

    public function _purge($searchableIds)
    {
        if ($engine = $this->getEnginePlugin()) {
            foreach ($searchableIds as $searchable_id) {
                $engine->searchEnginePurge($searchable_id);
            }
        }
    }

    private function _createSearchables($pluginName, $searchables)
    {
        $model = $this->getModel();
        foreach ($searchables as $search_name) {
            if (empty($search_name)) continue;
            $searchable = $model->create('Searchable');
            $searchable->name = $search_name;
            $searchable->plugin = $pluginName;
            $searchable->type = self::SEARCHABLE_TYPE_PUBLIC;
            $searchable->markNew();
            if ($searchable->commit()) {
                $searchable_ids[] = $searchable->id;
            }
        }

        // Link installed seachables with the current engine plugin
        if (!empty($searchable_ids)
            && ($engine_plugin = $this->getEnginePlugin())
            && ($engine = $model->Engine->criteria()->plugin_is($engine_plugin->name)->fetch()->getFirst())
        ) {
            foreach ($searchable_ids as $searchable_id) {
                $engine->linkSearchableById($searchable_id);
            }
        }
        
        $model->commit();
    }

    public function createOrUpdateSearchable($pluginName, $searchName, $searchTitle)
    {
        $model = $this->getModel();
        $searches = $model->Searchable
            ->criteria()
            ->plugin_is($pluginName)
            ->name_is($searchName)
            ->fetch();
        if ($searches->count() > 0) {
            $search = $searches->getFirst();
        } else {
            $search = $model->create('Searchable');
            $search->plugin = $pluginName;
            $search->name = $searchName;
            $search->default = 1;
            $search->last_import = time();
            $search->markNew();
        }
        $search->title = $searchTitle;
        return $model->commit();
    }

    private function _deleteSearchables($pluginName, $searchableName = null)
    {
        $ids = array();
        $model = $this->getModel();
        $criteria = $model->createCriteria('Searchable')->plugin_is($pluginName);
        if (isset($searchableName)) $criteria->name_is($searchableName);
        foreach ($model->Searchable->fetchByCriteria($criteria) as $entity) {
            $entity->markRemoved();
            $ids[] = $entity->id;
        }
        if (!$model->commit()) {
            return false;
        }
        return $ids;
    }

    public function deleteSearchable($pluginName, $searchableName)
    {
        if ($ids = $this->_deleteSearchables($pluginName, $searchableName)) {
            $this->_purge($ids);
        }
    }

    public function createSnippet($text, $keywords, $length = 255)
    {
        if ($length >= $text_len = strlen($text)) return $text;

        $regex = implode('|', array_map('preg_quote', $keywords));
        if (!preg_match('/' . $regex . '/i', $text, $matches, PREG_OFFSET_CAPTURE)) {
            return mb_strimlength($text, $start, $length);
        }

        list($matched, $matched_pos) = $matches[0];
        if (0 >= $start = $matched_pos - intval($length/2)) {
            return mb_strimlength($text, 0, $length);
        }

        $length = $length - 3; // subtract the prefix part: "..."
        if ($start + $length > $text_len) {
            $start = $text_len - $length;
        }
        return '...' . mb_strimlength($text, $start, $length);
    }

    public function highlightKeywords($text, $keywords, $tag = 'strong', $class = '')
    {
        $regex = implode('|', array_map('preg_quote', $keywords));
        $replacement = '<' . $tag . ' class="' . $class . '">$1</' . $tag . '>';
        return preg_replace('/(' . $regex . ')/i', $replacement, $text);
    }

    private function _getPluginSearchables($pluginName, $searchableName = null)
    {
        $model = $this->getModel();
        $criteria = $model->createCriteria('Searchable')->plugin_is($pluginName);
        if (isset($searchableName)) $criteria->name_is($searchableName);
        return $model->Searchable->fetchByCriteria($criteria);
    }

    /**
     * Registers a searchable content to the search engine
     *
     * @param string $pluginName
     * @param string $searchableName
     * @param int $contentId
     * @param string $title
     * @param string $content
     * @param int $userId
     * @param int $ctime
     * @param int $mtime
     * @param array $keywords
     * @param string $contentGroup
     * @return mixed 0 if no active searchable content or no search engine registered, true if success, and false otherwise
     */
    public function putContent($pluginName, $searchableName, $contentId, $title, $content, $userId, $ctime, $mtime, $keywords = array(), $contentGroup = '')
    {
        if (!$engine = $this->getEnginePlugin()) {
            return 0;
        }

        if (!$searchable = $this->_getPluginSearchables($pluginName, $searchableName)->getFirst()) return 0;

        return $engine->searchEnginePut($pluginName, $searchable->id, $contentId, $title, $content, $userId, $ctime, $mtime, $keywords, $contentGroup);
    }

    /**
     * Removes a registered content from the search engine
     *
     * @param string $pluginName
     * @param string $searchableName
     * @param int $contentId
     * @param string $contentGroup
     * @return mixed 0 if no active searchable content or no search engine registered, true if success, and false otherwise
     */
    public function purgeContent($pluginName, $searchableName, $contentId)
    {
        // content id cannot be empty
        if (empty($contentId)) return 0;

        if (!$engine = $this->getEnginePlugin()) {
            return 0;
        }

        if (!$searchable = $this->_getPluginSearchables($pluginName, $searchableName)->getFirst()) return 0;

        return $engine->searchEnginePurgeContent($searchable->id, $contentId);
    }

    /**
     * Removes contents by content group
     *
     * @param string $pluginName
     * @param string $contentGroup
     * @param string $searchableName
     * @return mixed 0 if no active searchable content or no search engine registered, true if success, and false otherwise
     */
    public function purgeContentGroup($pluginName, $contentGroup, $searchableName = null)
    {
        // content group cannot be empty
        if (empty($contentGroup)) return 0;

        if (!$engine = $this->getEnginePlugin()) {
            return 0;
        }

        $searchables = $this->_getPluginSearchables($pluginName, $searchableName);
        if ($searchables->count() == 0) return 0;

        foreach ($searchables as $searchable) {
            $engine->searchEnginePurgeContentGroup($searchable->id, $contentGroup);
        }
        return true;
    }

    private function _getActiveSearchables()
    {
        if ((!$engine_plugin = $this->getEnginePlugin())
            || (!$engine = $this->getModel()->Engine->criteria()->plugin_is($engine_plugin->name)->fetch(0, 0, 'plugin', 'ASC')->getFirst())
        ) {
            return array();
        }
        
        return $engine->Searchables;
    }

    public function getActiveSearchables($private = false)
    {
        $ret = array();
        foreach ($this->_getActiveSearchables() as $searchable) {
            if ($searchable->private != $private) continue;
            if ($plugin = $this->_application->getPlugin($searchable->plugin)) {
                $ret[$searchable->id] = array(
                    'name' => $searchable->name,
                    'title' => sprintf($plugin->searchGetNicename($searchable->name), $plugin->nicename),
                    'plugin' => $searchable->plugin,
                );
            }
        }
        return $ret;
    }

    public function getActiveSearchablePlugins($private = false)
    {
        $ret = array();
        foreach ($this->_getActiveSearchables() as $searchable) {
            if ($searchable->private != $private) continue;
            if ($plugin = $this->_application->getPlugin($searchable->plugin)) {
                $ret[$plugin->name] = $plugin->nicename;
            }
        }
        return $ret;
    }

    public function getMiniForm($pluginName = null)
    {
        // Do not diaplay on the search page
        if ($pluginName == $this->_name) return;

        // Create plugin select options if the search engine supports the find by plugin feature
        if ($this->getEnginePlugin()->searchEngineGetFeatures() & self::ENGINE_FEATURE_FIND_BY_PLUGINS) {
            $options = array(sprintf('<option value="">%s</option>', $this->_('Everything')));
            foreach ($this->_getActiveSearchables() as $searchable) {
                if ($plugin = $this->_application->getPlugin($searchable->plugin)) {
                    $plugin_name = $plugin->name;
                    if (isset($options[$plugin_name])) continue;
                    if (isset($pluginName) && $plugin_name == $pluginName) {
                        $options[$plugin_name] = sprintf('<option value="%s" selected="selected">%s</option>', h($plugin_name), h($plugin->nicename));
                    } else {
                        $options[$plugin_name] = sprintf('<option value="%s">%s</option>', h($plugin_name), h($plugin->nicename));
                    }
                }
            }
            $select = sprintf('<select name="p">%s</select>', implode("\n", $options));
        } else {
            $select = '';
        }

        return sprintf(
            '<form action="%s" method="get">
  <input type="text" name="keyword" />
  %s
  <input type="submit" value="%s" />
  <input type="hidden" name="%s" value="/search" />
</form>',
            $this->_application->createUrl(array(
                'base' => '/search',
                'fragment' => 'plugg-search-results'
            )),
            $select,
            $this->_('Search'),
            $this->_application->getRouteParam()
        );
    }
    
    public function getDefaultConfig()
    {
        return array(
            'searchEnginePlugin' => '',
            'keywordMinLength' => 3,
            'numResultsPage' => 20,
        );
    }
}
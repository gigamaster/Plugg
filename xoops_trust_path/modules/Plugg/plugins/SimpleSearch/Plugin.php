<?php
class Plugg_SimpleSearch_Plugin extends Plugg_Plugin implements Plugg_Search_Engine
{
    public function searchEngineGetFeatures()
    {
        return Plugg_Search_Plugin::ENGINE_FEATURE_BOOLEAN_AND
            | Plugg_Search_Plugin::ENGINE_FEATURE_BOOLEAN_OR
            | Plugg_Search_Plugin::ENGINE_FEATURE_FIND_BY_SEARCHABLES
            | Plugg_Search_Plugin::ENGINE_FEATURE_FIND_BY_PLUGINS
            | Plugg_Search_Plugin::ENGINE_FEATURE_ORDER_BY_DATE;
    }

    public function searchEngineGetSettings()
    {
        return array(
            'snippetLength' => array(
                '#title' => $this->_('Snippet length'),
                '#description' => $this->_('The length of search result snippets in bytes.'),
                '#default_value' => $this->getConfig('snippetLength'),
                '#required' => true,
                '#numeric' => true,
                '#size' => 5,
                '#field_suffix' => $this->_('bytes'),
            ),
        );
    }

    public function searchEngineFind($searchableIds, $keywords, $keywordsType, $keywordsNot, $limit, $offset, $order, $userId)
    {
        $where = $this->_getWhere($keywords, $keywordsType, $keywordsNot, $userId, $searchableIds);

        return $this->_search($where, $limit, $offset, $order, $keywords);
    }

    public function searchEngineCount($searchableIds, $keywords, $keywordsType, $keywordsNot, $userId)
    {
        $where = $this->_getWhere($keywords, $keywordsType, $keywordsNot, $userId, $searchableIds);

        return $this->_count($where);
    }

    public function searchEngineFindByPlugins($plugins, $keywords, $keywordsType, $keywordsNot, $limit, $offset, $order, $userId)
    {
        $where = $this->_getWhere($keywords, $keywordsType, $keywordsNot, $userId, array(), $plugins);

        return $this->_search($where, $limit, $offset, $order, $keywords);
    }

    public function searchEngineCountByPlugins($plugins, $keywords, $keywordsType, $keywordsNot, $userId)
    {
        $where = $this->_getWhere($keywords, $keywordsType, $keywordsNot, $userId, array(), $plugins);

        return $this->_count($where);
    }

    private function _getWhere($keywords, $keywordsType, $keywordsNot, $userId, $searchableIds = array(), $plugins = array())
    {
        $db = $this->getDB();
        $where = array();
        foreach ($keywords as $keyword) {
            $where[] = sprintf('(text_title LIKE %1$s OR text_body LIKE %1$s)', $db->escapeString('%' . h($keyword) . '%'));
        }
        $delimiter = Plugg_Search_Plugin::KEYWORDS_OR == $keywordsType ? ' OR ' : 'AND ';
        if (!empty($searchableIds)) {
            $ret = sprintf('text_searchable_id IN (%s) AND (%s)', implode(',', array_map('intval', $searchableIds)), implode($delimiter, $where));
        } elseif (!empty($plugins)) {
            $ret = sprintf('text_plugin IN (%s) AND (%s)', implode(',', array_map(array($db, 'escapeString'), $plugins)), implode($delimiter, $where));
        } else {
            $ret = sprintf('(%s)', implode($delimiter, $where));
        }
        if (!empty($userId)) $ret = sprintf('text_author_id = %d AND %s', $userId, $ret);

        return $ret;
    }

    private function _search($where, $limit, $offset, $order, $keywords)
    {
        $db = $this->getDB();
        $order_sql = $order == Plugg_Search_Plugin::ORDER_DATE_DESC ? 'DESC' : 'ASC';
        $sql = sprintf('SELECT * FROM %stext WHERE %s ORDER BY text_created %s', $db->getResourcePrefix(), $where, $order_sql);
        if ($rs = $db->query($sql, $limit, $offset)) {
            $search_plugin = $this->_application->getPlugin('Search');
            while ($row = $rs->fetchAssoc()) {
                $ret[] = array(
                    'plugin' => $row['text_plugin'],
                    'content_id' => $row['text_content_id'],
                    'created' => $row['text_ctime'],
                    'modified' => $row['text_mtime'],
                    'title_html' => $search_plugin->highlightKeywords(h($row['text_title']), $keywords),
                    'author_id' => $row['text_author_id'],
                    'searchable_id' => $row['text_searchable_id'],
                    'snippet_html' => $search_plugin->highlightKeywords(
                        $search_plugin->createSnippet($row['text_body'], $keywords, $this->getConfig('snippetLength')),
                        $keywords
                    ),
                );
            }
        }

        return $ret;
    }

    private function _count($where)
    {
        $db = $this->getDB();
        $sql = sprintf('SELECT COUNT(*) FROM %stext WHERE %s', $db->getResourcePrefix(), $where);
        if (!$rs = $db->query($sql)) return false;

        return $rs->fetchSingle();
    }

    public function searchEngineListBySearchContentIds($searchableId, $contentIds, $order)
    {
        $ret = array();
        $where = sprintf('text_searchable_id = %d AND text_content_id IN (%s)', $searchableId, implode(',', array_map('intval', $contentIds)));
        $db = $this->getDB();
        $sql = sprintf('SELECT text_content_id, text_title FROM %stext WHERE %s', $db->getResourcePrefix(), $where);
        if ($rs = $db->query($sql)) {
            while ($row = $rs->fetchRow()) {
                $ret[$row[0]] = $row[1];
            }
        }

        return $ret;
    }

    public function searchEnginePut($pluginName, $searchableId, $contentId, $title, $bodyHtml, $userId, $created, $modified, $keywords, $contentGroup)
    {
        $text = $this->_getText($searchableId, $contentId);
        $text->plugin = $pluginName;
        $text->title = $title;
        $text->body = strip_tags($bodyHtml);
        $text->author_id = $userId;
        $text->ctime = $created;
        $text->mtime = $modified;
        $text->content_group = $contentGroup;

        return $text->commit();
    }

    private function _getText($searchableId, $contentId)
    {
        $model = $this->getModel();
        $text = $model->Text
            ->criteria()
            ->searchableId_is($searchableId)
            ->contentId_is($contentId)
            ->fetch()
            ->getFirst();
        if (!$text) {
            $text = $model->create('Text');
            $text->searchable_id = $searchableId;
            $text->content_id = $contentId;
            $text->markNew();
        }

        return $text;
    }

    public function searchEnginePurge($searchableId)
    {
        $db = $this->getDB();
        $sql = sprintf('DELETE FROM %stext WHERE text_searchable_id = %d', $db->getResourcePrefix(), $searchableId);

        return $db->exec($sql, false);
    }

    public function searchEnginePurgeContent($searchableId, $contentId)
    {
        $db = $this->getDB();
        $sql = sprintf(
            'DELETE FROM %stext WHERE text_searchable_id = %d AND text_content_id = %d',
            $db->getResourcePrefix(), $searchableId, $contentId
        );

        return $db->exec($sql, false);
    }

    public function searchEnginePurgeContentGroup($searchableId, $contentGroup)
    {
        $db = $this->getDB();
        $sql = sprintf(
            'DELETE FROM %stext WHERE text_searchable_id = %d AND text_content_group LIKE %s',
            $db->getResourcePrefix(), $searchableId, $db->escapeString($contentGroup . '%')
        );

        return $db->exec($sql, false);
    }

    public function searchEngineUpdateIndex()
    {

    }

    /**
     * Overrides the parent method to use MyISAM tables instead of the default InnoDB if DB driver is MySQL
     *
     * @return Sabai_DB_Schema
     */
    protected function getDBSchema()
    {
        if (in_array($this->getDB()->getScheme(), array('MySQL', 'MySQLi'))) {
            return Sabai_DB_Schema::factory($this->getDB(), array(), array(), array('type' => 'MyISAM'));
        }

        return parent::_getDBSchema();
    }
    
    public function getDefaultConfig()
    {
        return array(
            'snippetLength' => 480
        );
    }
}
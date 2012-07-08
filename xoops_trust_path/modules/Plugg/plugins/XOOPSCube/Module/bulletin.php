<?php
class Plugg_XOOPSCube_Module_bulletin extends Plugg_XOOPSCube_Module implements Plugg_XOOPSCube_SearchableModule
{
    public function searchGetTitle()
    {
        return '';
    }
    
    function searchGetContentUrl($contentId)
    {
        // Check if the content exists
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf(
            'SELECT COUNT(storyid) FROM %s_stories WHERE storyid = %d',
            $db->prefix($this->_moduleDir),
            $contentId
        );
        if (($result = $db->query($sql))
            && ($row = $db->fetchRow($result))
            && $row[0] > 0
        ) {
            return sprintf(
                '%s/modules/%s/index.php?page=article&storyid=%d',
                XOOPS_URL, $this->_moduleDir, $contentId
            );
        }

        return false;
    }

    function searchFetchContents($limit, $offset)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf(
            'SELECT storyid, uid, title, created, published, html, smiley, br, xcode, hometext, bodytext
               FROM %1$s_stories
               ORDER BY storyid ASC',
            $db->prefix($this->_moduleDir)
        );
        if (!$result = $db->query($sql)) return array();

        return $this->_processResult($db, $result);
    }

    function searchCountContents()
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf('SELECT COUNT(storyid) FROM %s_stories', $db->prefix($this->_moduleDir));
        if ($result = $db->query($sql)) {
            if ($row = $db->fetchRow($result)) {
                return $row[0];
            }
        }

        return false;
    }

    function searchFetchContentsSince($timestamp, $limit, $offset)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf(
            'SELECT storyid, uid, title, created, published, html, smiley, br, xcode, hometext, bodytext
               FROM %s_stories
               WHERE created > %d
               ORDER BY storyid ASC',
            $db->prefix($this->_moduleDir),
            $timestamp
        );
        if (!$result = $db->query($sql)) return array();

        return $this->_processResult($db, $result);
    }

    function searchCountContentsSince($timestamp)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf('SELECT COUNT(storyid) FROM %s_stories WHERE created > %d', $db->prefix($this->_moduleDir), $timestamp);
        if ($result = $db->query($sql)) {
            if ($row = $db->fetchRow($result)) {
                return $row[0];
            }
        }

        return false;
    }

    function searchFetchContentsByIds($contentIds)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf(
            'SELECT storyid, uid, title, created, published, html, smiley, br, xcode, hometext, bodytext
               FROM %1$s_stories
               WHERE storyid IN (%2$s)
               ORDER BY storyid ASC',
            $db->prefix($this->_moduleDir),
            implode(',', array_map('intval', $contentIds))
        );
        if (!$result = $db->query($sql)) return array();

        return $this->_processResult($db, $result);
    }

    private function _processResult($db, $result)
    {
        $ret = array();
        $myts = MyTextSanitizer::getInstance();
        while ($row = $db->fetchArray($result)) {
            $body = $myts->displayTarea($row['hometext'], $row['html'], $row['smiley'], $row['br'], 1, $row['xcode'])
                . '<br /><br />'
                . $myts->displayTarea($row['bodytext'], $row['html'], $row['smiley'], $row['br'], 1, $row['xcode']);
            $ret[] = array(
                'id' => $row['storyid'],
                'user_id' => $row['uid'],
                'title' => $row['title'],
                'body' => $body,
                'created' => $row['created'],
                'modified' => $row['published'],
                'keywords' => array(),
                'group' => '',
            );
        }

        return $ret;
    }
}
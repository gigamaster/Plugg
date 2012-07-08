<?php
class Plugg_XOOPSCube_Module_newbb extends Plugg_XOOPSCube_Module implements Plugg_XOOPSCube_SearchableModule
{
    public function searchGetTitle()
    {
        return '';
    }
    
    public function searchGetContentUrl($contentId)
    {
        require_once $this->_moduleDocRoot . '/class/class.forumposts.php';
        $post = new ForumPosts($contentId);

        if (!$post->postid()) return false; // post no longer exists

        return sprintf('%1$s/modules/%2$s/viewtopic.php?post_id=%3$d&topic_id=%4$d&forum=%5$d#forumpost%3$d',
            XOOPS_URL, $this->_moduleDir, $post->postid(), $post->topic(), $post->forum());
    }

    public function searchFetchContents($limit, $offset)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf(
            'SELECT p.post_id, p.uid, p.subject, p.post_time, p.nohtml, p.nosmiley, t.post_text
               FROM %1$s_bb_posts p
               INNER JOIN %1$s_bb_posts_text t ON p.post_id = t.post_id
               ORDER BY p.post_id ASC',
            $db->prefix()
        );
        if (!$result = $db->query($sql)) return array();

        return $this->_processResult($db, $result);
    }

    public function searchCountContents()
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf(
            'SELECT COUNT(post_id) FROM %s_bb_posts',
            $db->prefix()
        );
        if ($result = $db->query($sql)) {
            if ($row = $db->fetchRow($result)) {
                return $row[0];
            }
        }

        return 0;
    }

    public function searchFetchContentsSince($timestamp, $limit, $offset)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf(
            'SELECT p.post_id, p.uid, p.subject, p.post_time, p.nohtml, p.nosmiley, t.post_text
               FROM %s_bb_posts p
               INNER JOIN %1$s_bb_posts_text t ON p.post_id = t.post_id
               WHERE p.post_time > %d
               ORDER BY p.post_id ASC',
            $db->prefix(),
            $timestamp
        );
        if (!$result = $db->query($sql)) return array();

        return $this->_processResult($db, $result);
    }

    public function searchCountContentsSince($timestamp)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf(
            'SELECT COUNT(post_id) FROM %s_bb_posts WHERE post_time > %d',
            $db->prefix(),
            $timestamp
        );
        if ($result = $db->query($sql)) {
            if ($row = $db->fetchRow($result)) {
                return $row[0];
            }
        }

        return 0;
    }

    public function searchFetchContentsByIds($contentIds)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf(
            'SELECT p.post_id, p.uid, p.subject, p.post_time, p.nohtml, p.nosmiley, t.post_text
               FROM %1$s_bb_posts p
               INNER JOIN %1$s_bb_posts_text t ON p.post_id = t.post_id
               WHERE p.post_id IN (%2$s)
               ORDER BY p.post_id ASC',
            $db->prefix(),
            implode(',', array_map('intval', $contentIds))
        );
        if (!$result = $db->query($sql)) return array();

        return $this->_processResult($db, $result);
    }

    protected function _processResult($db, $result)
    {
        $ret = array();
        $myts = MyTextSanitizer::getInstance();
        while ($row = $db->fetchArray($result)) {
            $ret[] = array(
                'id' => $row['post_id'],
                'user_id' => $row['uid'],
                'title' => $row['subject'],
                'body' => $myts->makeTareaData4Show($row['post_text'], !$row['nohtml'], !$row['nosmiley'], 1),
                'created' => $row['post_time'],
                'modified' => $row['post_time'],
                'keywords' => array(),
                'group' => '',
            );
        }

        return $ret;
    }
}
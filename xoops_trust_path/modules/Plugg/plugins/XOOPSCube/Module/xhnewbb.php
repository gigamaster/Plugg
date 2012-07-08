<?php
class Plugg_XOOPSCube_Module_xhnewbb extends Plugg_XOOPSCube_Module_newbb
{   
    function searchGetContentUrl($contentId)
    {
        require_once $this->_moduleDocRoot . '/class/class.forumposts.php';
        $post = new ForumPosts($contentId);

        if (!$post->postid()) return false; // post no longer exists

        return sprintf('%1$s/modules/%2$s/viewtopic.php?post_id=%3$d&topic_id=%4$d&forum=%5$d#forumpost%3$d',
            XOOPS_URL, $this->_moduleDir, $post->postid(), $post->topic(), $post->forum());
    }

    function searchFetchContents($limit, $offset)
    {
        $ret = array();
        $db = Database::getInstance();
        $sql = sprintf(
            'SELECT p.post_id, p.uid, p.subject, p.post_time, p.nohtml, p.nosmiley, t.post_text
               FROM %1$s_xhnewbb_posts p
               INNER JOIN %1$s_xhnewbb_posts_text t ON p.post_id = t.post_id
               ORDER BY p.post_id ASC',
            $db->prefix()
        );
        if ($result = $db->query($sql, $limit, $offset)) {
            $myts = MyTextSanitizer::getInstance();
            while ($row = $db->fetchRow($result)) {
                $ret[] = array(
                    'id' => $row[0],
                    'user_id' => $row[1],
                    'title' => $row[2],
                    'body' => $myts->makeTareaData4Show($row[6], !$row[4], !$row[5], 1),
                    'created' => $row[3],
                    'modified' => $row[3],
                    'keywords' => array(),
                    'group' => '',
                );
            }
        }

        return $ret;
    }

    function searchCountContents()
    {
        $db = Database::getInstance();
        if ($result = $db->query('SELECT COUNT(post_id) FROM '.$db->prefix('xhnewbb_posts'))) {
            if ($row = $db->fetchRow($result)) {
                return $row[0];
            }
        }

        return false;
    }

    function searchFetchContentsByIds($contentIds)
    {
        $ret = array();
        $db = Database::getInstance();
        $sql = sprintf(
            'SELECT p.post_id, p.uid, p.subject, p.post_time, p.nohtml, p.nosmiley, t.post_text
               FROM %1$s_xhnewbb_posts p
               INNER JOIN %1$s_xhnewbb_posts_text t ON p.post_id = t.post_id
               WHERE p.post_id IN (%2$s)
               ORDER BY p.post_id ASC',
            $db->prefix(),
            implode(',', array_map('intval', $contentIds))
        );
        if ($result = $db->query($sql)) {
            $myts = MyTextSanitizer::getInstance();
            while ($row = $db->fetchRow($result)) {
                $ret[] = array(
                    'id' => $row[0],
                    'user_id' => $row[1],
                    'title' => $row[2],
                    'body' => $myts->makeTareaData4Show($row[6], !$row[4], !$row[5], 1),
                    'created' => $row[3],
                    'modified' => $row[3],
                    'keywords' => array(),
                    'group' => '',
                );
            }
        }

        return $ret;
    }
    
    function searchFetchContentsSince($timestamp, $limit, $offset)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf(
            'SELECT p.post_id, p.uid, p.subject, p.post_time, p.nohtml, p.nosmiley, t.post_text
               FROM %s_xhnewbb_posts p
               INNER JOIN %1$s_xhnewbb_posts_text t ON p.post_id = t.post_id
               WHERE p.post_time > %d
               ORDER BY p.post_id ASC',
            $db->prefix(),
            $timestamp
        );
        if (!$result = $db->query($sql)) return array();

        return $this->_processResult($db, $result);
    }

    function searchCountContentsSince($timestamp)
    {
        $db = XoopsDatabaseFactory::getDatabaseConnection();
        $sql = sprintf(
            'SELECT COUNT(post_id) FROM %s_xhnewbb_posts WHERE post_time > %d',
            $db->prefix(),
            $timestamp
        );
        if ($result = $db->query($sql)) {
            if ($row = $db->fetchRow($result)) {
                return $row[0];
            }
        }

        return false;
    }
}
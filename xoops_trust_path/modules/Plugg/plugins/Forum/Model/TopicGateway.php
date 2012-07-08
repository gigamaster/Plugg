<?php
class Plugg_Forum_Model_TopicGateway extends Plugg_Forum_Model_Base_TopicGateway
{
    public function getStarredTopicIds($userId, $groupId = null, $limit = 20, $offset = 0, $sort = 'last_posted', $order = 'DESC')
    {
        $sql = sprintf(
            'SELECT t.topic_id
               FROM %1$stopic t
               INNER JOIN %1$sstar s ON s.star_topic_id = t.topic_id
               WHERE s.star_user_id = %2$d',
            $this->_db->getResourcePrefix(),
            $userId
        );
        if (!empty($groupId)) $sql .= ' AND t.topic_group_id = ' . intval($groupId);
        $sql .= sprintf(' ORDER BY t.topic_sticky DESC, t.topic_%s %s', $sort, $order);
        
        $ret = array();
        if ($rs = $this->_db->query($sql, $limit, $offset)) {
            while ($row = $rs->fetchRow(0)) {
                $ret[] = $row[0];
            }
        }

        return new ArrayObject($ret);
    }
    
    public function getStarredTopicCount($userId, $groupId = null)
    {
        $sql = sprintf(
            'SELECT COUNT(*)
               FROM %1$stopic t
               INNER JOIN %1$sstar s ON s.star_topic_id = t.topic_id
               WHERE s.star_user_id = %2$d',
            $this->_db->getResourcePrefix(),
            $userId
        );
        if (!empty($groupId)) $sql .= ' AND t.topic_group_id = ' . intval($groupId);
        
        if (!$rs = $this->_db->query($sql)) {
            return 0;
        }

        return $rs->fetchSingle();
    }
}
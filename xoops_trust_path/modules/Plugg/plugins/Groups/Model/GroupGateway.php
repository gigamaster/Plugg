<?php
class Plugg_Groups_Model_GroupGateway extends Plugg_Groups_Model_Base_GroupGateway
{
    public function getPopularGroupMemberCount($limit, $offset = 0)
    {
        $ret = array();
        $sql = sprintf(
            'SELECT g.group_id, COUNT(*) AS member_count FROM %1$sgroup g
               INNER JOIN %1$smember m ON m.member_group_id = g.group_id
               WHERE g.group_status = %2$d AND m.member_status = %3$d
               GROUP BY g.group_id
               ORDER BY member_count DESC',
            $this->_db->getResourcePrefix(),
            Plugg_Groups_Plugin::GROUP_STATUS_APPROVED,
            Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE
        );
        if ($rs = $this->_db->query($sql, $limit, $offset)) {
            while ($row = $rs->fetchRow(0)) {
                $ret[$row[0]] = $row[1];
            }
        }

        return $ret;
    }
}
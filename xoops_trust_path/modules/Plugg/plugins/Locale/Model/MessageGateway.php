<?php
class Plugg_Locale_Model_MessageGateway extends Plugg_Locale_Model_Base_MessageGateway
{
    function deleteByIds($ids)
    {
        $sql = sprintf('DELETE FROM %smessage WHERE message_id IN (%s)', $this->_db->getResourcePrefix(), implode(',', array_map('intval', $ids)));
        if (!$this->_db->exec($sql)) {
            return false;
        }
        return $this->_db->affectedRows();
    }
    
    function getPluginMessageCount($lang = SABAI_LANG)
    {
        $ret = array();
        $sql = sprintf('SELECT message_plugin, COUNT(*) FROM %smessage WHERE message_lang = %s GROUP BY message_plugin', $this->_db->getResourcePrefix(), $this->_db->escapeString($lang));
        if ($rs = $this->_db->query($sql)) {
            while ($row = $rs->fetchRow()) {
                $ret[$row[0]] = $row[1];
            }
        }
        return $ret;
    }
}
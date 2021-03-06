<?php
/*
This file has been generated by the Sabai scaffold script. Do not edit this file directly.
If you need to customize the class, use the following file:
pluginsy/Groups/Model/GroupGateway.php
*/
abstract class Plugg_Groups_Model_Base_GroupGateway extends Sabai_Model_Gateway
{
    public function getName()
    {
        return 'group';
    }

    public function getFields()
    {
        return array('group_id' => Sabai_Model::KEY_TYPE_INT, 'group_created' => Sabai_Model::KEY_TYPE_INT, 'group_updated' => Sabai_Model::KEY_TYPE_INT, 'group_name' => Sabai_Model::KEY_TYPE_TEXT, 'group_display_name' => Sabai_Model::KEY_TYPE_VARCHAR, 'group_description' => Sabai_Model::KEY_TYPE_TEXT, 'group_description_html' => Sabai_Model::KEY_TYPE_TEXT, 'group_description_filter_id' => Sabai_Model::KEY_TYPE_INT, 'group_type' => Sabai_Model::KEY_TYPE_INT, 'group_avatar' => Sabai_Model::KEY_TYPE_VARCHAR, 'group_avatar_medium' => Sabai_Model::KEY_TYPE_VARCHAR, 'group_avatar_thumbnail' => Sabai_Model::KEY_TYPE_VARCHAR, 'group_avatar_icon' => Sabai_Model::KEY_TYPE_VARCHAR, 'group_status' => Sabai_Model::KEY_TYPE_INT, 'group_user_id' => Sabai_Model::KEY_TYPE_INT, 'group_member_count' => Sabai_Model::KEY_TYPE_INT, 'group_member_last' => Sabai_Model::KEY_TYPE_INT, 'group_member_lasttime' => Sabai_Model::KEY_TYPE_INT);
    }

    protected function _getSelectByIdQuery($id, $fields)
    {
        return sprintf(
            'SELECT %s FROM %sgroup WHERE group_id = %d',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $id
        );
    }

    protected function _getSelectByIdsQuery($ids, $fields)
    {
        return sprintf(
            'SELECT %s FROM %sgroup WHERE group_id IN (%s)',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            implode(',', array_map('intval', $ids))
        );
    }

    protected function _getSelectByCriteriaQuery($criteriaStr, $fields)
    {
        return sprintf(
            'SELECT %1$s FROM %2$sgroup WHERE %3$s',
            empty($fields) ? '*' : implode(', ', $fields),
            $this->_db->getResourcePrefix(),
            $criteriaStr
        );
    }

    protected function _getInsertQuery($values)
    {
        $values['group_created'] = time();
        $values['group_updated'] = 0;
        $values['group_member_lasttime'] = $values['group_created'];
        return sprintf("INSERT INTO %sgroup(group_created, group_updated, group_name, group_display_name, group_description, group_description_html, group_description_filter_id, group_type, group_avatar, group_avatar_medium, group_avatar_thumbnail, group_avatar_icon, group_status, group_user_id, group_member_count, group_member_last, group_member_lasttime) VALUES(%d, %d, %s, %s, %s, %s, %d, %d, %s, %s, %s, %s, %d, %d, %d, %d, %d)", $this->_db->getResourcePrefix(), $values['group_created'], $values['group_updated'], $this->_db->escapeString($values['group_name']), $this->_db->escapeString($values['group_display_name']), $this->_db->escapeString($values['group_description']), $this->_db->escapeString($values['group_description_html']), $values['group_description_filter_id'], $values['group_type'], $this->_db->escapeString($values['group_avatar']), $this->_db->escapeString($values['group_avatar_medium']), $this->_db->escapeString($values['group_avatar_thumbnail']), $this->_db->escapeString($values['group_avatar_icon']), $values['group_status'], $values['group_user_id'], $values['group_member_count'], $values['group_member_last'], $values['group_member_lasttime']);
    }

    protected function _getUpdateQuery($id, $values)
    {
        $last_update = $values['group_updated'];
        $values['group_updated'] = time();
        return sprintf("UPDATE %sgroup SET group_updated = %d, group_name = %s, group_display_name = %s, group_description = %s, group_description_html = %s, group_description_filter_id = %d, group_type = %d, group_avatar = %s, group_avatar_medium = %s, group_avatar_thumbnail = %s, group_avatar_icon = %s, group_status = %d, group_user_id = %d WHERE group_id = %d AND group_updated = %d", $this->_db->getResourcePrefix(), $values['group_updated'], $this->_db->escapeString($values['group_name']), $this->_db->escapeString($values['group_display_name']), $this->_db->escapeString($values['group_description']), $this->_db->escapeString($values['group_description_html']), $values['group_description_filter_id'], $values['group_type'], $this->_db->escapeString($values['group_avatar']), $this->_db->escapeString($values['group_avatar_medium']), $this->_db->escapeString($values['group_avatar_thumbnail']), $this->_db->escapeString($values['group_avatar_icon']), $values['group_status'], $values['group_user_id'], $id, $last_update);
    }

    protected function _getDeleteQuery($id)
    {
        return sprintf('DELETE FROM %1$sgroup WHERE group_id = %2$d', $this->_db->getResourcePrefix(), $id);
    }

    protected function _getUpdateByCriteriaQuery($criteriaStr, $sets)
    {
        $sets['group_updated'] = 'group_updated=' . time();
        return sprintf('UPDATE %sgroup SET %s WHERE %s', $this->_db->getResourcePrefix(), implode(',', $sets), $criteriaStr);
    }

    protected function _getDeleteByCriteriaQuery($criteriaStr)
    {
        return sprintf('DELETE FROM %1$sgroup WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }

    protected function _getCountByCriteriaQuery($criteriaStr)
    {
        return sprintf('SELECT COUNT(*) FROM %1$sgroup WHERE %2$s', $this->_db->getResourcePrefix(), $criteriaStr);
    }

    protected function _beforeDeleteTrigger1($id, $old)
    {
        return $this->_db->exec(sprintf('DELETE FROM %1$smember WHERE %1$smember.member_group_id = %2$d', $this->_db->getResourcePrefix(), $id), false);
    }

    protected function _beforeDeleteTrigger2($id, $old)
    {
        return $this->_db->exec(sprintf('DELETE FROM %1$sactivewidget WHERE %1$sactivewidget.activewidget_group_id = %2$d', $this->_db->getResourcePrefix(), $id), false);
    }

    protected function _beforeDeleteTrigger($id, $old)
    {
        if (!$this->_beforeDeleteTrigger1($id, $old)) return false;
        if (!$this->_beforeDeleteTrigger2($id, $old)) return false;
        return true;
    }
}
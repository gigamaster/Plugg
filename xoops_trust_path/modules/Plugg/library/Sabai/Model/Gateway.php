<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Model
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Model
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
abstract class Sabai_Model_Gateway
{
    /**
     * @var Sabai_DB
     */
    protected $_db;

    public function setDB(Sabai_DB $db)
    {
        $this->_db = $db;
    }

    public function getTableName()
    {
        return $this->_db->getResourcePrefix() . $this->getName();
    }

    /**
     * @return array All fields used within this gateway
     */
    public function getAllFields()
    {
        return array_merge($this->getSortFields(), $this->getFields());
    }

    public function selectById($id, array $fields = array())
    {
        return $this->_db->query($this->_getSelectByIdQuery($id, $fields));
    }

    public function selectByIds(array $ids, array $fields = array())
    {
        return $this->_db->query($this->_getSelectByIdsQuery($ids, $fields));
    }

    /**
     * Selects a row data by PK from the main table. For normal entities, this
     * is exactly the same as calling selectById(). Tree entities require this
     * query because selectById() will fail on after Insert trigger when the
     * tree table is not yet filled with data associated with the main table.
     *
     * @param string $id
     * @param array $fields
     * @return Sabai_DB_Rowset
     */
    public function selectByIdFromMainTable($id, array $fields = array())
    {
        return $this->selectById($id, $fields);
    }

    public function selectByCriteria(Sabai_Model_Criteria $criteria, array $fields = array(), $limit = 0, $offset = 0, array $sort = null, array $order = null, $group = null)
    {
        $criteria_str = '';
        $criteria->acceptGateway($this, $criteria_str);
        $query = $this->_getSelectByCriteriaQuery($criteria_str, $fields);
        return $this->selectBySQL($query, $limit, $offset, $sort, $order, $group);
    }

    public function insert(array $values)
    {
        if ($this->_db->isTriggerEnabled()) {
            if (!$this->_db->exec($this->_getInsertQuery($values))) return false;

            return $this->_db->lastInsertId($this->getTableName(), $this->getName() . '_id');
        }
        return $this->_insertWithTrigger($values);
    }

    protected function _insertWithTrigger($values)
    {
        if (!$this->_beforeInsertTrigger($values) ||
            !$this->_db->exec($this->_getInsertQuery($values))) return false;

        $id = $this->_db->lastInsertId($this->getTableName(), $this->getName() . '_id');
        if (!$rs = $this->selectByIdFromMainTable($id)) {
            // this should not happen here,
            return false;
        }
        $this->_afterInsertTrigger($id, $rs->fetchAssoc());
        return $id;
    }

    protected function _beforeInsertTrigger($new)
    {
        return true;
    }

    protected function _afterInsertTrigger($id, $new){}

    public function updateById($id, $values)
    {
        if ($this->_db->isTriggerEnabled()) {
            return $this->_db->exec($this->_getUpdateQuery($id, $values));
        }
        return $this->_updateWithTrigger($id, $values);
    }

    protected function _updateWithTrigger($id, $values)
    {
        if (!$rs = $this->selectById($id)) return false;

        $old = $rs->fetchAssoc();
        if (!$this->_beforeUpdateTrigger($id, $values, $old) ||
            !$this->_db->exec($this->_getUpdateQuery($id, $values))) return false;

        $this->_afterUpdateTrigger($id, $values, $old);
        return true;
    }

    protected function _beforeUpdateTrigger($id, $new, $old)
    {
        return true;
    }

    protected function _afterUpdateTrigger($id, $new, $old){}

    public function deleteById($id)
    {
        if ($this->_db->isTriggerEnabled()) return $this->_db->exec($this->_getDeleteQuery($id));

        return $this->_deleteWithTrigger($id);
    }

    protected function _deleteWithTrigger($id)
    {
        if (!$rs = $this->selectById($id)) return false;

        $old = $rs->fetchAssoc();
        if (!$this->_beforeDeleteTrigger($id, $old) ||
            !$this->_db->exec($this->_getDeleteQuery($id))) return false;

        $this->_afterDeleteTrigger($id, $old);
        return true;
    }

    protected function _beforeDeleteTrigger($id, $old)
    {
        return true;
    }

    protected function _afterDeleteTrigger($id, $old){}

    /**
     * Enter description here...
     *
     * @param Sabai_Model_Criteria $criteria
     * @param array $values
     * @return mixed number of affected rows on success, false on failure
     */
    public function updateByCriteria(Sabai_Model_Criteria $criteria, array $values)
    {
        $sets = array();
        $fields = $this->getFields();
        foreach (array_keys($values) as $k) {
            if (isset($fields[$k])) {
                $operator = '=';
                $this->_sanitizeForQuery($values[$k], $fields[$k], $operator);
                $sets[$k] = $k . $operator . $values[$k];
            }
        }
        $criteria_str = '';
        $criteria->acceptGateway($this, $criteria_str);

        return $this->_db->exec($this->_getUpdateByCriteriaQuery($criteria_str, $sets));
    }

    public function deleteByCriteria(Sabai_Model_Criteria $criteria)
    {
        $criteria_str = '';
        $criteria->acceptGateway($this, $criteria_str);

        return $this->_db->exec($this->_getDeleteByCriteriaQuery($criteria_str));
    }

    public function countByCriteria(Sabai_Model_Criteria $criteria, $group = null)
    {
        $criteria_str = '';
        $criteria->acceptGateway($this, $criteria_str);
        $sql = $this->_getCountByCriteriaQuery($criteria_str);
        if (!empty($group)) {
            $fields = $this->getAllFields();
            if (isset($fields[$group])) $sql .= ' GROUP BY ' . $group;
        }
        if ($rs = $this->_db->query($sql)) return $rs->fetchSingle();

        return 0;
    }

    public function selectBySQL($sql, $limit = 0, $offset = 0, array $sort = null, array $order = null, $group = null)
    {
        if (!empty($group)) {
            $fields = $this->getFields();
            if (isset($fields[$group])) $sql .= ' GROUP BY ' . $group;
        }
        if (!empty($sort)) {
            $sort_fields = $this->getSortFields();
            foreach (array_keys($sort) as $i) {
                if (isset($sort_fields[$sort[$i]])) {
                    $order_by[] = $sort[$i] . ' ' . (isset($order[$i]) && $order[$i] == 'DESC' ? 'DESC': 'ASC');
                }
            }
            if (isset($order_by)) $sql .= ' ORDER BY ' . implode(',', $order_by);
        }

        return $this->_db->query($sql, $limit, $offset);
    }

    public function visitCriteriaEmpty(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $criteriaStr .= '1=1';
    }

    public function visitCriteriaComposite(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        if ($criteria->isEmpty()) {
            $this->visitCriteriaEmpty($criteria, $criteriaStr, $validateField);
            return;
        }
        $elements = $criteria->getElements();
        $count = count($elements);
        $conditions = $criteria->getConditions();
        $criteriaStr .= '(';
        $elements[0]->acceptGateway($this, $criteriaStr, $validateField);
        for ($i = 1; $i < $count; $i++) {
            $criteriaStr .= ' ' . $conditions[$i] . ' ';
            $elements[$i]->acceptGateway($this, $criteriaStr, $validateField);
        }
        $criteriaStr .= ')';
    }

    public function visitCriteriaCompositeNot(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $criteriaStr .= 'NOT ' . $this->visitCriteriaComposite($criteria, $criteriaStr, $validateField);
    }

    protected function _visitCriteriaValue(Sabai_Model_Criteria $criteria, &$criteriaStr, $operator, $validateField)
    {
        $key = $criteria->getKey();
        $data_type = null;
        if ($validateField) {
            $fields = $this->getAllFields();
            if (!isset($fields[$key])) return;

            $data_type = $fields[$key];
        }
        $value = $criteria->getValue();
        if ($criteria->isSanitizeRequired()) {
            $this->_sanitizeForQuery($value, $data_type, $operator);
        }
        $criteriaStr .= "$key $operator $value";
    }

    public function visitCriteriaIs(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $this->_visitCriteriaValue($criteria, $criteriaStr, '=', $validateField);
    }

    public function visitCriteriaIsNot(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $this->_visitCriteriaValue($criteria, $criteriaStr, '!=', $validateField);
    }

    public function visitCriteriaIsSmallerThan(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $this->_visitCriteriaValue($criteria, $criteriaStr, '<', $validateField);
    }

    public function visitCriteriaIsGreaterThan(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $this->_visitCriteriaValue($criteria, $criteriaStr, '>', $validateField);
    }

    function visitCriteriaIsOrSmallerThan(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $this->_visitCriteriaValue($criteria, $criteriaStr, '<=', $validateField);
    }

    public function visitCriteriaIsOrGreaterThan(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $this->_visitCriteriaValue($criteria, $criteriaStr, '>=', $validateField);
    }

    public function visitCriteriaIsNull(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $key = $criteria->getKey();
        if ($validateField) {
            $fields = $this->getAllFields();
            if (!isset($fields[$key])) return;
        }
        $criteriaStr .= $key . ' IS NULL';
    }

    protected function _visitCriteriaArray(Sabai_Model_Criteria $criteria, &$criteriaStr, $format, $validateField)
    {
        $key = $criteria->getKey();
        $data_type = null;
        if ($validateField) {
            $fields = $this->getAllFields();
            if (!isset($fields[$key])) return;

            $data_type = $fields[$key];
        }
        $values = $criteria->getValueArray();
        if (!empty($values)) {
            $operator = null;
            foreach ($values as $v) {
                $this->_sanitizeForQuery($v, $data_type, $operator);
                $value[] = $v;
            }
            $criteriaStr .= sprintf($format, $key, implode(',', $value));
        }
    }

    public function visitCriteriaIn(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $this->_visitCriteriaArray($criteria, $criteriaStr, '%s IN (%s)', $validateField);
    }

    public function visitCriteriaNotIn(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $this->_visitCriteriaArray($criteria, $criteriaStr, '%s NOT IN (%s)', $validateField);
    }

    protected function _visitCriteriaStr(Sabai_Model_Criteria $criteria, &$criteriaStr, $format, $validateField)
    {
        $key = $criteria->getKey();
        $data_type = null;
        if ($validateField) {
            $fields = $this->getAllFields();
            if (!isset($fields[$key])) return;

            $data_type = $fields[$key];
        }
        $value = sprintf($format, $criteria->getValueStr());
        $operator = 'LIKE';
        $this->_sanitizeForQuery($value, $data_type, $operator);
        $criteriaStr .= "$key LIKE $value";
    }

    public function visitCriteriaStartsWith(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $this->_visitCriteriaStr($criteria, $criteriaStr, '%s%%', $validateField);
    }

    public function visitCriteriaEndsWith(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $this->_visitCriteriaStr($criteria, $criteriaStr, '%%%s', $validateField);
    }

    public function visitCriteriaContains(Sabai_Model_Criteria $criteria, &$criteriaStr, $validateField)
    {
        $this->_visitCriteriaStr($criteria, $criteriaStr, '%%%s%%', $validateField);
    }

    /**
     * @param mixed $value
     * @param int $dataType
     * @param string $operator
     */
    protected function _sanitizeForQuery(&$value, $dataType = null, &$operator)
    {
        switch ($dataType) {
            case Sabai_Model::KEY_TYPE_INT_NULL:
                if (is_numeric($value)) {
                    $value = intval($value);
                } else {
                    $value = 'NULL';
                    $operator = ($operator == '!=') ? 'IS NOT' : 'IS';
                }
                break;
            case Sabai_Model::KEY_TYPE_INT:
                $value = intval($value);
                break;
            case Sabai_Model::KEY_TYPE_FLOAT:
                $value = floatval($value);
                break;
            case Sabai_Model::KEY_TYPE_BOOL:
                $value = $this->_db->escapeBool($value);
                break;
            case Sabai_Model::KEY_TYPE_BLOB:
                $value = $this->_db->escapeBlob($value);
                break;
            default:
                $value = $this->_db->escapeString($value);
                break;
        }
    }

    /**
     * Gets the fields that can be used for sorting.
     * This method will only be overwritten by assoc entities.
     *
     * @return array
     */
    public function getSortFields()
    {
        return $this->getFields();
    }

    /**
     * Gets the last error message returned by the database driver
     *
     * @return string
     */
    public function getError()
    {
        return $this->_db->lastError();
    }

    abstract public function getName();
    abstract public function getFields();
    abstract protected function _getSelectByIdQuery($id, $fields);
    abstract protected function _getSelectByIdsQuery($ids, $fields);
    abstract protected function _getSelectByCriteriaQuery($criteriaStr, $fields);
    abstract protected function _getInsertQuery($values);
    abstract protected function _getUpdateQuery($id, $values);
    abstract protected function _getDeleteQuery($id);
    abstract protected function _getUpdateByCriteriaQuery($criteriaStr, $sets);
    abstract protected function _getDeleteByCriteriaQuery($criteriaStr);
    abstract protected function _getCountByCriteriaQuery($criteriaStr);
}
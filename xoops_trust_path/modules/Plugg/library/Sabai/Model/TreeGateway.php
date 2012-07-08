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

require_once 'Sabai/Model/Gateway.php';

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
abstract class Sabai_Model_TreeGateway extends Sabai_Model_Gateway
{
    public function selectByCriteria(Sabai_Model_Criteria $criteria, array $fields = array(), $limit = 0, $offset = 0, $sort = null, $order = null, $group = null)
    {
        $sort = array_merge((array)$sort, array(strtolower($this->getName()) . '_tree_left'));
        $order = array_merge((array)$order, array('ASC'));
        return parent::selectByCriteria($criteria, $fields, $limit, $offset, $sort, $order);
    }

    /**
     * @param string $id
     * @param array $fields
     * @return Sabai_DB_Rowset
     */
    public function selectDescendants($id, array $fields = array())
    {
        return $this->selectBySQL($this->_getSelectDescendantsQuery($id, $fields), 0, 0, array(strtolower($this->getName()) . '_tree_left'), array('ASC'));
    }

    /**
     * @param string $id
     * @param array $fields
     * @return Sabai_DB_Rowset
     */
    public function selectDescendantsAsTree($id, array $fields = array())
    {
        return $this->selectBySQL($this->_getSelectDescendantsAsTreeQuery($id, $fields), 0, 0, array(strtolower($this->getName()) . '_tree_left'), array('ASC'));
    }

    /**
     * @param string $id
     * @return int
     */
    public function countDescendants($id)
    {
        if ($rs = $this->_db->query($this->_getCountDescendantsQuery($id))) {
            return $rs->fetchSingle();
        }
        return false;
    }

    /**
     * @param array $ids
     * @return Sabai_DB_Rowset
     */
    public function countDescendantsByIds($ids)
    {
        return $this->_db->query($this->_getCountDescendantsByIdsQuery($ids));
    }

    /**
     * @param string $id
     * @param array $fields
     * @return Sabai_DB_Rowset
     */
    public function selectParents($id, array $fields = array())
    {
        return $this->selectBySQL($this->_getSelectParentsQuery($id, $fields), 0, 0, array(strtolower($this->getName()) . '_tree_left'), array('ASC'));
    }

    /**
     * @param string $id
     * @return int
     */
    public function countParents($id)
    {
        if ($rs = $this->_db->query($this->_getCountParentsQuery($id))) {
            return $rs->fetchSingle();
        }
        return false;
    }

    /**
     * @param array $ids
     * @return Sabai_DB_Rowset
     */
    public function countParentsByIds($ids)
    {
        return $this->_db->query($this->_getCountParentsByIdsQuery($ids));
    }
}
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

require_once 'Sabai/Model/EntityRepository.php';

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
abstract class Sabai_Model_TreeEntityRepository extends Sabai_Model_EntityRepository
{
    /**
     * Constructor
     *
     * @param string $name
     * @param Sabai_Model $model
     * @return Sabai_Model_TreeEntityRepository
     */
    function __construct($name, Sabai_Model $model)
    {
        parent::__construct($name, $model);
    }

    /**
     * Fetches all children entities
     *
     * @param string $id
     * @return Sabai_Model_EntityCollection
     */
    public function fetchDescendantsByParent($id)
    {
        return $this->_getCollection($this->_model->getGateway($this->getName())->selectDescendants($id, array()));
    }

    /**
     * Fetches all children entities with a 'level' value
     *
     * @param string $id
     * @return Sabai_Model_EntityCollection
     */
    public function fetchDescendantsAsTreeByParent($id)
    {
        $it = $this->_getCollection($this->_model->getGateway($this->getName())->selectDescendants($id, array()));
        require_once 'Sabai/Model/EntityCollection/Decorator/ParentEntitiesCount.php';
        return new Sabai_Model_EntityCollection_Decorator_ParentEntitiesCount($this->getName(), $it, $this->_model);
        // below uses subquery which is not supported in < MySQL 4.1
        // should we check version here?
        // return $this->_getCollection($this->_model->getGateway($this->getName())->selectDescendantsAsTree($id, array()));
    }

    /**
     * Fetches all entities with a 'level' value
     *
     * @return Sabai_Model_EntityCollection
     */
    public function fetchAsTree()
    {
        require_once 'Sabai/Model/EntityCollection/Decorator/ParentEntitiesCount.php';
        return new Sabai_Model_EntityCollection_Decorator_ParentEntitiesCount($this->getName(), $this->fetch(), $this->_model);
    }

    /**
     * Counts all children entities
     *
     * @param string $id
     * @return int
     */
    public function countDescendantsByParent($id)
    {
        return $this->_model->getGateway($this->getName())->countDescendants($id);
    }

    /**
     * Fethces all parent entities
     *
     * @param string $id
     * @return Sabai_Model_EntityCollection
     */
    public function fetchParents($id)
    {
        return $this->_getCollection($this->_model->getGateway($this->getName())->selectParents($id, array()));
    }

    /**
     * Counts all parent entities
     *
     * @param string $id
     * @return int
     */
    public function countParents($id)
    {
        return $this->_model->getGateway($this->getName())->countParents($id);
    }

    /**
     * Counts all parent entities
     *
     * @param array $ids
     * @return array
     */
    public function countParentsByIds($ids)
    {
        $ret = array();
        if ($rs = $this->_model->getGateway($this->getName())->countParentsByIds($ids)) {
            while ($row = $rs->fetchRow()) {
                $ret[$row[0]] = $row[1];
            }
        }
        return $ret;
    }

    /**
     * Counts all descendant entities
     *
     * @param array $ids
     * @return array
     */
    public function countDescendantsByIds($ids)
    {
        $ret = array();
        if ($rs = $this->_model->getGateway($this->getName())->countDescendantsByIds($ids)) {
            while ($row = $rs->fetchRow()) {
                $ret[$row[0]] = $row[1];
            }
        }
        return $ret;
    }
}
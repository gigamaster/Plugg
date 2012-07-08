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
abstract class Sabai_Model_EntityRepository
{
    /**
     * @var string
     */
    private $_name;
    /**
     * @var string
     */
    private $_fieldPrefix;
    /**
     * @var Sabai_Model
     */
    protected $_model;
    /**
     * @var array
     */
    private $_entityCache = array();
    private $_criteria;

    /**
     * Constructor
     */
    protected function __construct($name, Sabai_Model $model)
    {
        $this->_name = $name;
        $this->_fieldPrefix = strtolower($name) . '_';
        $this->_model = $model;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Creates a criteria element and returns self for chainability
     *
     * @return Sabai_Model_EntityRepository
     */
    public function criteria()
    {
        $this->_criteria = $this->_model->createCriteria($this->_name);
        return $this;
    }

    /**
     * Calls a method defined in the criteria element and returns self for chainability
     *
     * @return Sabai_Model_EntityRepository
     */
    public function __call($method, $args)
    {
        $this->_criteria = call_user_func_array(array($this->_criteria, $method), $args);
        return $this;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function isEntityCached($id)
    {
        return isset($this->_entityCache[$id]) ? $this->_entityCache[$id] : false;
    }

    /**
     * @param Sabai_Model_Entity
     */
    public function cacheEntity(Sabai_Model_Entity $entity)
    {
        $this->_entityCache[$entity->id] = $entity;
    }

    /**
     */
    public function clearCache()
    {
        $this->_entityCache = array();
    }

    /**
     * @param string $id
     * @param bool $noCache
     * @return Sabai_Model_Entity
     */
    public function fetchById($id, $noCache = false)
    {
        if ($noCache) {
            unset($this->_entityCache[$id]);

            return $this->_getCollection($this->_model->getGateway($this->_name)->selectById($id))->getFirst();
        }

        if ($entity = $this->isEntityCached($id)) return $entity;

        return $this->_getCollection($this->_model->getGateway($this->_name)->selectById($id))->getFirst();
    }

    /**
     * @param array $ids
     * @return Sabai_Model_EntityCollection
     */
    public function fetchByIds($ids)
    {
        $entities = $this->_getCollection($this->_model->getGateway($this->_name)->selectByIds($ids));
        // Merge the current cached entities with the fetched ones
        $this->_entityCache = $entities->getArray() + $this->_entityCache;

        return $entities;
    }

    /**
     * @param Sabai_Model_Criteria
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return Sabai_Model_EntityCollection_Rowset
     */
    public function fetchByCriteria(Sabai_Model_Criteria $criteria, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        return $this->_getCollection(
            $this->_model->getGateway($this->getName())
                ->selectByCriteria($criteria, array(), $limit, $offset, array_map(array($this, '_prefixSort'), (array)$sort), (array)$order)
        );
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return Sabai_Model_EntityCollection_Rowset
     */
    public function fetch($limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $criteria = !isset($this->_criteria) ? Sabai_Model_Criteria::createEmpty() : $this->_criteria;
        unset($this->_criteria);
        return $this->fetchByCriteria($criteria, $limit, $offset, $sort, $order);
    }

    /**
     * @param Sabai_Model_Criteria
     * @param string $group
     * @return int
     */
    public function countByCriteria(Sabai_Model_Criteria $criteria, $group = null)
    {
        return $this->_model->getGateway($this->getName())->countByCriteria($criteria, $group);
    }

    /**
     * @return int
     * @param string $group
     */
    public function count($group = null)
    {
        $criteria = !isset($this->_criteria) ? Sabai_Model_Criteria::createEmpty() : $this->_criteria;
        unset($this->_criteria);
        return $this->countByCriteria($criteria, $group);
    }

    /**
     * @param Sabai_Model_Criteria $criteria
     * @param int $perpage
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @param string $group
     * @return Sabai_Model_PageCollection_Criteria
     */
    public function paginateByCriteria(Sabai_Model_Criteria $criteria, $perpage = 10, $sort = null, $order = null)
    {
        require_once 'Sabai/Model/PageCollection/Criteria.php';
        return new Sabai_Model_PageCollection_Criteria($this, $criteria, $perpage, $sort, $order);
    }

    /**
     * @param Sabai_Model_Criteria $criteria
     * @param int $perpage
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return Sabai_Model_PageCollection_Criteria
     */
    public function paginate($perpage = 10, $sort = null, $order = null)
    {
        $criteria = !isset($this->_criteria) ? Sabai_Model_Criteria::createEmpty() : $this->_criteria;
        unset($this->_criteria);
        return $this->paginateByCriteria($criteria, $perpage, $sort, $order);
    }

    /**
     * Helper method for fetching entitie pages by foreign key relationship
     *
     * @param string $entityName
     * @param string $id
     * @param int $perpage
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return Sabai_Model_PageCollection_Entity
     */
    protected function _paginateByEntity($entityName, $id, $perpage = 10, $sort = null, $order = null)
    {
        if ($criteria = @$this->_criteria) {
            unset($this->_criteria);
            return $this->_paginateByEntityAndCriteria($entityName, $id, $criteria, $perpage, $sort, $order);
        }

        require_once 'Sabai/Model/PageCollection/Entity.php';
        return new Sabai_Model_PageCollection_Entity($this, $entityName, $id, $perpage, $sort, $order);
    }

    /**
     * Helper method for fetching entitie pages by entitiy id and criteria
     *
     * @param string $entityName
     * @param string $id
     * @param Sabai_Model_Criteria
     * @param int $perpage
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return Sabai_Model_PageCollection_Entity
     */
    protected function _paginateByEntityAndCriteria($entityName, $id, Sabai_Model_Criteria $criteria, $perpage = 10, $sort = null, $order = null)
    {
        require_once 'Sabai/Model/PageCollection/EntityCriteria.php';
        return new Sabai_Model_PageCollection_EntityCriteria($this, $entityName, $id, $criteria, $perpage, $sort, $order);
    }

    /**
     * Helper method for fetching entities by foreign key relationship
     *
     * @param string $foreignKey
     * @param string $id
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return Sabai_Model_PageCollection_Entity
     */
    protected function _fetchByForeign($foreignKey, $id, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        if ($criteria = @$this->_criteria) {
            unset($this->_criteria);
            return $this->_fetchByForeignAndCriteria($foreignKey, $id, $criteria, $limit, $offset, $sort, $order);
        }

        $criteria = is_array($id) ? Sabai_Model_Criteria::createIn($foreignKey, $id) : Sabai_Model_Criteria::createValue($foreignKey, $id);
        return $this->fetchByCriteria($criteria, $limit, $offset, $sort, $order);
    }

    /**
     * Helper method for counting entities by foreign key relationship
     *
     * @param string $foreignKey
     * @param string $id
     * @param string $group
     * @return int
     */
    protected function _countByForeign($foreignKey, $id, $group = null)
    {
        if ($criteria = @$this->_criteria) {
            unset($this->_criteria);
            return $this->_countByForeignAndCriteria($foreignKey, $id, $criteria, $group);
        }

        $criteria = is_array($id) ? Sabai_Model_Criteria::createIn($foreignKey, $id) : Sabai_Model_Criteria::createValue($foreignKey, $id);
        return $this->countByCriteria($criteria, $group);
    }

    /**
     * Helper method for fetching entities by foreign key relationship
     *
     * @param string $foreignKey
     * @param string $id
     * @param Sabai_Model_Criteria $criteria
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return Sabai_Model_PageCollection_Entity
     */
    protected function _fetchByForeignAndCriteria($foreignKey, $id, Sabai_Model_Criteria $criteria, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $criterion = Sabai_Model_Criteria::createComposite(array($criteria));
        if (is_array($id)) {
            $criterion->addAnd(Sabai_Model_Criteria::createIn($foreignKey, $id));
        } else {
            $criterion->addAnd(Sabai_Model_Criteria::createValue($foreignKey, $id));
        }

        return $this->fetchByCriteria($criterion, $limit, $offset, $sort, $order);
    }

    /**
     * Helper method for counting entities by foreign key relationship
     *
     * @param string $foreignKey
     * @param string $id
     * @param Sabai_Model_Criteria
     * @param string $group
     * @return int
     */
    protected function _countByForeignAndCriteria($foreignKey, $id, Sabai_Model_Criteria $criteria, $group = null)
    {
        $criterion = Sabai_Model_Criteria::createComposite(array($criteria));
        if (is_array($id)) {
            $criterion->addAnd(Sabai_Model_Criteria::createIn($foreignKey, $id));
        } else {
            $criterion->addAnd(Sabai_Model_Criteria::createValue($foreignKey, $id));
        }

        return $this->countByCriteria($criterion, $group);
    }

    /**
     * Helper method for fetching entities by association table relationship
     *
     * @param string $selfTable
     * @param string $assocEntity
     * @param string $assocTargetKey
     * @param string $id
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return Sabai_Model_EntityCollection_Rowset
     */
    protected function _fetchByAssoc($selfTable, $assocEntity, $assocTargetKey, $id, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        if ($criteria = @$this->_criteria) {
            unset($this->_criteria);
            return $this->_fetchByAssocAndCriteria($selfTable, $assocEntity, $assocTargetKey, $id, $criteria, $limit, $offset, $sort, $order);
        }

        $criteria = is_array($id) ? Sabai_Model_Criteria::createIn($assocTargetKey, $id) : Sabai_Model_Criteria::createValue($assocTargetKey, $id);
        $fields = array('DISTINCT ' . $selfTable . '.*');

        return $this->_getCollection(
            $this->_model->getGateway($assocEntity)
                ->selectByCriteria($criteria, $fields, $limit, $offset, array_map(array($this, '_prefixSort'), (array)$sort), $order)
        );
    }

    /**
     * Helper method for counting entities by association table relationship
     *
     * @param string $selfTableId
     * @param string $assocEntity
     * @param string $assocTargetKey
     * @param string $id
     * @param string $group
     * @return int
     */
    protected function _countByAssoc($selfTableId, $assocEntity, $assocTargetKey, $id, $group = null)
    {
        if ($criteria = @$this->_criteria) {
            unset($this->_criteria);
            return $this->_countByAssocAndCriteria($selfTableId, $assocEntity, $assocTargetKey, $id, $criteria, $group);
        }

        $criteria = is_array($id) ? Sabai_Model_Criteria::createIn($assocTargetKey, $id) : Sabai_Model_Criteria::createValue($assocTargetKey, $id);

        return $this->_model->getGateway($assocEntity)->selectByCriteria($criteria, array('COUNT(DISTINCT '. $selfTableId .')'))->fetchSingle();
    }

    /**
     * Helper method for fetching entities by association table relationship
     * and additional criteria
     *
     * @param string $selfTable
     * @param string $assocEntity
     * @param string $assocTargetKey
     * @param string $id
     * @param Sabai_Model_Criteria $criteria
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return Sabai_Model_EntityCollection_Rowset
     */
    protected function _fetchByAssocAndCriteria($selfTable, $assocEntity, $assocTargetKey, $id, Sabai_Model_Criteria $criteria, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $criterion = Sabai_Model_Criteria::createComposite(array($criteria));
        if (is_array($id)) {
            $criterion->addAnd(Sabai_Model_Criteria::createIn($assocTargetKey, $id));
        } else {
            $criterion->addAnd(Sabai_Model_Criteria::createValue($assocTargetKey, $id));
        }
        $fields = array('DISTINCT ' . $selfTable . '.*');

        return $this->_getCollection(
            $this->_model->getGateway($assocEntity)
                ->selectByCriteria($criterion, $fields, $limit, $offset, array_map(array($this, '_prefixSort'), (array)$sort), (array)$order)
        );
    }

    /**
     * Helper method for counting entities by association table relationship
     * and additional criteria
     *
     * @param string $selfTableId
     * @param string $assocEntity
     * @param string $id
     * @param Sabai_Model_Criteria $criteria
     * @param string $group
     * @return Sabai_Model_EntityCollection_Rowset
     */
    protected function _countByAssocAndCriteria($selfTableId, $assocEntity, $assocTargetKey, $id, Sabai_Model_Criteria $criteria, $group = null)
    {
        $criterion = Sabai_Model_Criteria::createComposite(array($criteria));
        if (is_array($id)) {
            $criterion->addAnd(Sabai_Model_Criteria::createIn($assocTargetKey, $id));
        } else {
            $criterion->addAnd(Sabai_Model_Criteria::createValue($assocTargetKey, $id));
        }

        return $this->_model->getGateway($assocEntity)->selectByCriteria($criterion, array('COUNT(DISTINCT '. $selfTableId .')'), $group)->fetchSingle();
    }

    /**
     * Prefix the requested sort value get the actual field name
     *
     * @param string $sort
     * @return array
     */
    private function _prefixSort($sort)
    {
        return $this->_fieldPrefix . $sort;
    }

    /**
     * Turns a rowset object into an entity collection object
     *
     * @param mixed Sabai_DB_Rowset or false
     * @return Sabai_Model_EntityCollection
     */
    protected function _getCollection($rs)
    {
        if (!$rs instanceof Sabai_DB_Rowset) {
            $collection = $this->createCollection();
        } else {
            $collection = $this->_getCollectionByRowset($rs);
        }

        return $collection;
    }

    /**
     * @param Sabai_DB_Rowset $rs
     * @return Sabai_Model_EntityCollection
     */
    abstract protected function _getCollectionByRowset(Sabai_DB_Rowset $rs);
    /**
     * @param array $entities
     * @return Sabai_Model_EntityCollection
     */
    abstract public function createCollection(array $entities = array());
}
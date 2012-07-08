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
abstract class Sabai_Model_Entity
{
    /**
     * @var string
     */
    private $_name;
    /**
     * @var Sabai_Model
     */
    protected $_model;
    /**
     * @var array
     */
    protected $_vars = array();
    /**
     * @var array
     */
    protected $_objects = array();
    /**
     * @var array
     */
    private $_counts = array();
    /**
     * @var string
     */
    private $_tempId = false;
    /**
     * Entities that this entity should be assigned on commit
     * @var array
     */
    private $_entitiesToAssign = array();
    /**
     * Emtities that should be assigned to this entity on commit
     * @var array
     */
    protected $_entitiesToBeAssigned = array();

    /**
     * Constructor
     *
     * @param string $name
     * @param Sabai_Model $model
     * @return Sabai_Model_Entity
     */
    protected function __construct($name, Sabai_Model $model)
    {
        $this->_name = $name;
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
     * @return Sabai_Model
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Shortcut method for getting the related Sabai_Model_EntityRepository object
     *
     * @return Sabai_Model_EntityRepository
     */
    protected function _getRepository()
    {
        return $this->_model->getRepository($this->getName());
    }

    /**
     * @param string $value
     */
    public function setTempId($value)
    {
        $this->_tempId = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getTempId()
    {
        return $this->_tempId;
    }

    /**
     * @param array $arr
     */
    public function initVars($arr)
    {
        foreach (array_keys($arr) as $name) {
            $this->_initVar($name, $arr[$name]);
        }
        return $this;
    }

    /**
     * @param string $key
     * @param string $sort
     * @param string $order
     * @return mixed
     */
    public function get($key, $sort = null, $order = null, $limit = 0, $offset = 0)
    {
        return $this->_get($key, $sort, $order, $limit, $offset);
    }

    public function __get($key)
    {
        return $this->_get($key, null, null);
    }
    
    abstract protected function _get($name, $sort, $order, $limit = 0, $offset = 0);

    /**
     * @param mixed $key string or an associative array
     * @param mixed $value
     * @param bool $markDirty
     */
    public function set($key, $value = null, $markDirty = true)
    {
        if (is_array($key)) {
            foreach ($key as $name => $value) {
                $this->_set($name, $value, false);
            }
            if ($markDirty) $this->markDirty();
        } else {
            $this->_set($key, $value, $markDirty);
        }

        return $this;
    }

    public function __set($key, $value)
    {
        $this->_set($key, $value, true);
    }
    
    abstract protected function _set($name, $value, $markDirty);

    /**
     * @return array
     */
    public function getVars()
    {
        return $this->_vars;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param bool $markDirty
     */
    protected function _setVar($name, $value, $markDirty = true)
    {
        $this->_vars[$name] = $value;
        if ($markDirty) $this->markDirty();
    }

    /**
     * Sets an object related to this entity
     *
     * @param string $name
     * @param object $object
     */
    public function setObject($name, $object)
    {
        $this->_objects[$name] = $object;
        return $this;
    }

    /**
     * Gets an object related to this entity
     *
     * @param string $name
     * @return object
     */
    public function getObject($name)
    {
        return $this->_objects[$name];
    }

    /**
     * Clears a cached object related to this entity
     *
     * @param string $name
     */
    public function clearObject($name)
    {
        unset($this->_objects[$name]);
        return $this;
    }

    /**
     * Sets a number of related entity count
     *
     * @param string $name
     * @param int $count
     */
    public function setCount($name, $count)
    {
        $this->_counts[$name] = $count;
        return $this;
    }

    /**
     * Gets the number of related entity count
     *
     * @param string $name
     * @return int
     */
    public function getCount($name)
    {
        return $this->_counts[$name];
    }

    /**
     */
    public function markNew()
    {
        $this->_model->registerNew($this);
        return $this;
    }

    /**
     */
    public function markDirty()
    {
        $this->_model->registerDirty($this);
        return $this;
    }

    /**
     */
    public function markRemoved()
    {
        $this->_model->registerRemoved($this);
        // this is so that no entities can be assigned during commit
        $this->_entitiesToBeAssigned = array();
        return $this;
    }

    /**
     */
    public function cache()
    {
        $this->_getRepository()->cacheEntity($this);
        return $this;
    }

    /**
     * @param string $entityName
     * @param string $foreignKey
     * @return Sabai_Model_Entity
     */
    protected function _fetchEntity($entityName, $foreignKey)
    {
        if (!isset($this->_objects[$entityName])) {
            if ($id = $this->$foreignKey) {
                if (!$this->_objects[$entityName] = $this->_model->getRepository($entityName)->fetchById($id)) {
                    // warn because this should not happen usually
                    Sabai_Log::warn(sprintf('%s entity with ID %d does not exist', $entityName, $id), __FILE__, __LINE__);
                }
            } else {
                $this->_objects[$entityName] = false;
            }
        }

        return $this->_objects[$entityName];
    }

    /**
     * @param Sabai_Model_Entity $entity
     * @param string $foreignKey
     */
    protected function _assignEntity($entity, $foreignKey)
    {
        $entity_name = $entity->getName();
        if (!$id = $entity->id) {
            if (!$temp_id = $entity->getTempId()) {
                $entity->markNew();
                $temp_id = $entity->getTempId();
            }
            $entity->addEntityToAssign($this);
            $this->_entitiesToBeAssigned[$entity_name][$foreignKey] = $temp_id;
        } else {
            if ($this->$foreignKey == $id) {
                trigger_error(sprintf('Trying to assign an already assigned %s entity, skipping operation',
                                      $entity->getName()),
                              E_USER_NOTICE);
            } else {
                if ($temp_id = $entity->getTempId()) {
                    // temp id is set, meaning that the entity is being assigned on commit
                    // check if we are really allowed to assgin this entity
                    if (!isset($this->_entitiesToBeAssigned[$entity_name][$foreignKey]) ||
                        $this->_entitiesToBeAssigned[$entity_name][$foreignKey] != $temp_id
                    ) {
                        return;
                    }
                }

                // Assign entity
                $this->$foreignKey = $id;
                unset($this->_entitiesToBeAssigned[$entity_name][$foreignKey]);
            }
        }
    }

    /**
     * @param string $entityName
     * @param string $foreignKey
     * @return bool
     */
    protected function _unassignEntity($entityName, $foreignKey)
    {
        $this->set($foreignKey, null);
        unset($this->_entitiesToBeAssigned[$entityName][$foreignKey]);
    }

    public function getEntitiesToBeAssigned()
    {
        return $this->_entitiesToBeAssigned;
    }

    /**
     * @param string $entityName
     * @param int $limit
     * @param int $offset
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return Sabai_Model_EntityCollection_Rowset
     */
    protected function _fetchEntities($entityName, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        if ($limit != 0 && $offset != 0) {
            $method = 'fetchBy' . $this->getName();
            // Do not cache if range is retrieving only a part of all entities
            return $this->_model->getRepository($entityName)->$method($this->id, $limit, $offset, $sort, $order);
        }

        if (!isset($this->_objects[$entityName])) {
            $method = 'fetchBy' . $this->getName();
            $this->_objects[$entityName] = $this->_model->getRepository($entityName)->$method($this->id, $limit, $offset, $sort, $order);
        }

        return $this->_objects[$entityName];
    }

    /**
     * @param string $entityName
     * @param int $perpage
     * @param mixed $sort An array or string
     * @param mixed $order An array or string
     * @return Sabai_Model_PageCollection_Entity
     */
    protected function _paginateEntities($entityName, $perpage = 10, $sort = null, $order = null)
    {
        $method = 'paginateBy' . $this->getName();
        return $this->_model->getRepository($entityName)->$method($this->id, $perpage, $sort, $order);
    }

    /**
     * @param string $entityName
     * @return int
     */
    protected function _countEntities($entityName)
    {
        if (!isset($this->_counts[$entityName])) {
            if (!$id = $this->id) {
                $this->_counts[$entityName] = 0;
            } else {
                $method = 'countBy' . $this->getName();
                $this->_counts[$entityName] = $this->_model->getRepository($entityName)->$method($id);
            }
        }
        return $this->_counts[$entityName];
    }

    /**
     * @param Sabai_Model_Entity $entity
     * @return bool
     */
    protected function _addEntity(Sabai_Model_Entity $entity)
    {
        $method = 'assign' . $this->getName();
        $entity->$method($this);
    }

    /**
     * @param string $targetPrimaryKey
     * @param string $entityName
     * @param string $id
     * @return int
     */
    protected function _removeEntityById($targetPrimaryKey, $entityName, $id)
    {
        $method = 'fetchBy' . $this->getName() . 'AndCriteria';
        $criteria = Sabai_Model_Criteria::createValue($targetPrimaryKey, $id);
        $targets = $this->_model->getRepository($entityName)->$method($this->id, $criteria);
        foreach ($targets as $target) {
            $method = 'unassign' . $this->getName();
            $target->$method($this);
        }
        return $targets->count();
    }

    /**
     * @param string $entityName
     * @return int
     */
    protected function _removeEntities($entityName)
    {
        $entities = $this->_fetchEntities($entityName);
        $method = 'unassign' . $this->getName();
        foreach ($entities as $entity) {
            $entity->$method($this);
        }
        return $entities->count();
    }

    /**
     * @param string $entityName
     * @return Sabai_Model_Entity
     */
    protected function _createEntity($entityName)
    {
        $entity = $this->_model->create($entityName);
        $method = 'add' . $entityName;
        $this->$method($entity);
        return $entity;
    }

    /**
     * @param string $linkEntityName
     * @param string $linkTargetKey
     * @param string $id
     * @return object Sabai_Model_Entity
     */
    protected function _linkEntityById($linkEntityName, $linkTargetKey, $id)
    {
        if (!$id = intval($id)) {
            return;
        }
        $link = $this->_model->create($linkEntityName);
        $method = 'assign' . $this->getName();
        $link->$method($this);
        $link->$linkTargetKey = $id;
        $link->markNew();
        return $link;
    }

    /**
     * @param string $linkEntityName
     * @param string $linkSelfKey
     * @param string $linkTargetKey
     * @param string $id
     */
    protected function _unlinkEntityById($linkEntityName, $linkSelfKey, $linkTargetKey, $id)
    {
        if (!$id = intval($id)) {
            return;
        }
        $criteria = Sabai_Model_Criteria::createComposite()
            ->addAnd(Sabai_Model_Criteria::createValue($linkSelfKey, $this->id))
            ->addAnd(Sabai_Model_Criteria::createValue($linkTargetKey, $id));
        $links = $$this->_model->getRepository($linkEntityName)->fetchByCriteria($criteria);
        foreach ($links as $link) {
            $link->markRemoved();
        }
        return $links->count();
    }

    /**
     * @param string $linkEntityName
     */
    protected function _unlinkEntities($linkEntityName)
    {
        $method = 'fetchBy' . $this->getName();
        $links = $this->_model->getRepository($linkEntityName)->$method($this->id);
        foreach ($links as $link) {
            $link->markRemoved();
        }
        return $links->count();
    }

    /**
     * @param Sabai_Model_Entity $entity
     */
    public function addEntityToAssign(Sabai_Model_Entity $entity)
    {
        $this->_entitiesToAssign[] = $entity;

        return $this;
    }

    /**
     */
    public function clearEntitiesToAssign()
    {
        $this->_entitiesToAssign = array();

        return $this;
    }

    /**
     * @return array
     */
    public function getEntitiesToAssign()
    {
        return $this->_entitiesToAssign;
    }

    /**
     * Commits the changes made to this entity. You must call markNew() or markRemoved()
     * prior to this method to insert or delete this entity.
     *
     * @return bool
     */
    public function commit()
    {
        return $this->_model->commitOne($this) === 1;
    }

    /**
     * Reloads vars from the repository
     *
     * @return bool
     */
    function reload()
    {
        $this->_vars = $this->_getRepository()
           ->fetchById($this->id, true)
           ->getVars();

        return $this;
    }
}
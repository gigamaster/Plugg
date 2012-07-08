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
 * Sabai_Model_Entity
 */
require 'Sabai/Model/Entity.php';
/**
 * Sabai_Model_EntityRepository
 */
require 'Sabai/Model/EntityRepository.php';
/**
 * Sabai_Model_EntityCollection_Rowset
 */
require 'Sabai/Model/EntityCollection/Array.php';
/**
 * Sabai_Model_EntityCollection_Rowset
 */
require 'Sabai/Model/EntityCollection/Rowset.php';
/**
 * Sabai_Model_Criteria
 */
require 'Sabai/Model/Criteria.php';
/**
 * Sabai_Model_Gateway
 */
require 'Sabai/Model/Gateway.php';

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
class Sabai_Model
{
    const COMMIT_ERROR_NONE = 0, COMMIT_ERROR_NEW = 1, COMMIT_ERROR_DIRTY = 2, COMMIT_ERROR_REMOVED = 3;

    const KEY_TYPE_INT = 1, KEY_TYPE_INT_NULL = 2, KEY_TYPE_CHAR = 5, KEY_TYPE_VARCHAR = 7,
        KEY_TYPE_TEXT = 10, KEY_TYPE_FLOAT = 15, KEY_TYPE_BOOL = 20, KEY_TYPE_BLOB = 25;

    /**
     * @var Sabai_DB
     */
    protected $_db;
    /**
     * @var array
     */
    private $_repositories = array();
    /**
     * @var array
     */
    private $_gateways = array();
    /**
     * @var array
     */
    private $_entities = array();
    /**
     * @var string
     */
    private $_commitError;
    /**
     * @var Sabai_Model_Entity
     */
    private $_commitErrorEntity;
    /**
     * @var int
     */
    private $_commitErrorType = self::COMMIT_ERROR_NONE;
    /**
     * @var string
     */
    protected $_modelPrefix;
    /**
     * Path to directory where compiled/custom model files are located
     *
     * @var string
     */
    protected $_modelDir;

    /**
     * Constructor
     *
     * @param Sabai_DB $db
     * @param string $modelDir
     * @param string $modelPrefix
     * @return Sabai_Model
     */
    public function __construct(Sabai_DB $db, $modelDir, $modelPrefix = '')
    {
        $this->_db = $db;
        $this->_modelDir = $modelDir;
        $this->_modelPrefix = $modelPrefix;
    }

    /**
     * @return string
     */
    public function getModelPrefix()
    {
        return $this->_modelPrefix;
    }

    /**
     * @return string
     */
    public function getModelDir()
    {
        return $this->_modelDir;
    }

    /**
     * PHP magic method
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getRepository($name);
    }

    /**
     * Gets an instance of Sabai_Model_EntityRepository
     *
     * @param string $name
     * @return Sabai_Model_EntityRepository
     */
    public function getRepository($name)
    {
        $name_lc = strtolower($name);
        if (!isset($this->_repositories[$name_lc])) {
            $this->_repositories[$name_lc] = $this->_getRepository($name);
        }
        return $this->_repositories[$name_lc];
    }

    /**
     * Gets an instance of Sabai_Model_EntityRepository
     *
     * @param string $name
     * @return Sabai_Model_EntityRepository
     */
    protected function _getRepository($name)
    {
        $class = $this->_modelPrefix . $name . 'Repository';
        if (!class_exists($class, false)) {
            $file = $name . '.php';
            require $this->_modelDir . '/Base/' . $file;
            require $this->_modelDir . '/' . $file;
        }
        return new $class($this);
    }

    /**
     * Gets an instance of Sabai_Model_Gateway
     *
     * @param string $name
     * @return Sabai_Model_Gateway
     */
    public function getGateway($name)
    {
        $name_lc = strtolower($name);
        if (!isset($this->_gateways[$name_lc])) {
            $this->_loadGateway($name, $name_lc);
        }
        return $this->_gateways[$name_lc];
    }

    /**
     * Loads an instance of Sabai_Model_Gateway
     *
     * @param string $name
     * @param string $as
     * @return Sabai_Model_Gateway
     */
    protected function _loadGateway($name, $as)
    {
        $class = $this->_modelPrefix . $name . 'Gateway';
        if (!class_exists($class, false)) {
            $file = $name . 'Gateway.php';
            require $this->_modelDir . '/Base/' . $file;
            require $this->_modelDir . '/' . $file;
        }
        $this->_gateways[$as] = new $class();
        $this->_gateways[$as]->setDB($this->_db);
    }

    /**
     * Creates a collection of entity objects
     *
     * @param string $name
     * @param array $entities
     * @return Sabai_Model_EntityCollection
     */
    public function createCollection($name, array $entities = array())
    {
        return $this->getRepository($name)->createCollection($entities);
    }

    /**
     * Decorates a collection of entity objects
     *
     * @param Sabai_Model_EntityCollection $collection
     * @param mixed $with
     * @return Sabai_Model_EntityCollection
     */
    public function decorate($collection, $with)
    {
        if (is_array($with)) {
            list($with_name, $with_args) = $with;
        } else {
            $with_name = $with;
        }
        $class = $this->_modelPrefix . $collection->getName() . 'With' . $with_name;
        if (!class_exists($class, false)) {
            require $this->_modelDir . '/' . $collection->getName() . 'With' . $with_name . '.php';
        }
        if (!empty($with_args)) {
            $reflection = new ReflectionClass($class);

            return $reflection->newInstanceArgs(array_merge(array($collection), $with_args));
        }

        return new $class($collection);
    }

    /**
     * Gets an instance of Sabai_Model_EntityCriteria
     *
     * @param string $name
     * @return Sabai_Model_EntityCriteria
     */
    public function createCriteria($name)
    {
        if (!class_exists('Sabai_Model_EntityCriteria', false)) {
            require 'Sabai/Model/EntityCriteria.php';
        }
        $class = $this->_modelPrefix . 'Base_' . $name . 'Criteria';
        if (!class_exists($class, false)) {
            require $this->_modelDir . '/Base/' . $name . 'Criteria.php';
        }
        return new $class();
    }

    /**
     * Gets an array of property names defined for a Sabai_Model_Entity class
     *
     * @param string $entityName
     * @return array
     */
    public function getPropertyNamesFor($entityName)
    {
        $this->getRepository($entityName);
        $class = $this->_modelPrefix . 'Base_' . $entityName;
        return call_user_func(array($class, 'propertyNames'));
    }

    /**
     * Gets an array of local property names defined for a Sabai_Model_Entity class
     *
     * @param string $entityName
     * @param array $filter
     * @return array
     */
    public function getLocalPropertyNamesFor($entityName, array $filter = array())
    {
        $this->getRepository($entityName);
        $class = $this->_modelPrefix . 'Base_' . $entityName;
        $ret = call_user_func(array($class, 'localPropertyNames'));
        if (!empty($filter)) {
            $ret = array_intersect_key($ret, array_flip($filter));
        }
        return $ret;
    }

    /**
     * Creates a new entity
     *
     * @param string $entityName
     * @return Sabai_Model_Entity
     */
    public function create($entityName)
    {
        $this->getRepository($entityName);
        $class = $this->_modelPrefix . $entityName;
        return new $class($this);
    }

    /**
     * Registers a new instance of Sabai_Model_Entity
     *
     * @param Sabai_Model_Entity $entity
     */
    public function registerNew(Sabai_Model_Entity $entity)
    {
        if ($entity->id) {
            trigger_error(sprintf('Cannot add existent entity(ID:%s) as new', $entity->id), E_USER_WARNING);
            return;
        }
        if ($entity->getTempId()) return; // already registered as new

        $name = $entity->getName();
        if (!isset($this->_entities['new'][$name])) {
            $this->_entities['new'][$name] = array();
            $temp_id = 1;
        } else {
            $temp_id = count($this->_entities['new'][$name]) + 1;
        }

        $entity->setTempId($temp_id);
        $this->_entities['new'][$name][$temp_id] = $entity;
    }

    /**
     * Registers a modified(drity) instance of Sabai_Model_Entity
     *
     * @param Sabai_Model_Entity $entity
     */
    public function registerDirty(Sabai_Model_Entity $entity)
    {
        if ($entity->getTempId()) return; // already registered as new

        if (!$id = $entity->id) return; // invalid

        $name = $entity->getName();
        if (isset($this->_entities['removed'][$name][$id])) return; // already removed

        $this->_entities['dirty'][$name][$id] = $entity;
    }

    /**
     * Registers a deleted instance of Sabai_Model_Entity
     *
     * @param Sabai_Model_Entity $entity
     */
    public function registerRemoved(Sabai_Model_Entity $entity)
    {
        $name = $entity->getName();
        if ($temp_id = $entity->getTempId()) {
            // registered as new, so remove it from there
            unset($this->_entities['new'][$name][$temp_id]);
            return;
        }
        if (!$id = $entity->id) {
            // invalid
            trigger_error('Cannot register non-existent entity as removed', E_USER_WARNING);
            return;
        }
        if (isset($this->_entities['dirty'][$name][$id])) {
            unset($this->_entities['dirty'][$name][$id]);
        }
        $this->_entities['removed'][$name][$id] = $entity;
    }

    /**
     * Commits pending Sabai_Model_Entity instances to the datasource
     *
     * @return mixed integer if success, false otherwise
     */
    public function commit()
    {

        $this->_db->beginTransaction();
        // new entities should be commited first to properly create foreign key mapping
        if ((false === $new = $this->_commitNew()) ||
            (false === $removed = $this->_commitRemoved()) ||
            (false === $dirty = $this->_commitDirty())
        ) {
            $this->_db->rollback();
            return false;
        }
        $this->_db->commit();
        $this->_entities = array();
        foreach (array_keys($this->_repositories) as $i) {
            $this->_repositories[$i]->clearCache();
        }

        return $new + $removed + $dirty;
    }

    /**
     * Commits a pending Sabai_Model_Entity instance to the datasource
     *
     * @param Sabai_Model_Entity $entity
     * @return mixed 1 if success, false otherwise
     */
    public function commitOne(Sabai_Model_Entity $entity)
    {
        $result = true;
        $name = $entity->getName();
        $this->_db->beginTransaction();
        if ($temp_id = $entity->getTempId()) {
            if (isset($this->_entities['new'][$name][$temp_id])) {
                unset($this->_entities['new'][$name][$temp_id]);
                $result = $this->_commitOneNew($this->getGateway($name), $entity);
            }
        } elseif ($id = $entity->id) {
            if (isset($this->_entities['dirty'][$name][$id])) {
                unset($this->_entities['dirty'][$name][$id]);
                $result = $this->_commitOneDirty($this->getGateway($name), $entity);
            } elseif (isset($this->_entities['removed'][$name][$id])) {
                unset($this->_entities['removed'][$name][$id]);
                $result = $this->_commitOneRemoved($this->getGateway($name), $entity);
            }
        }
        if (!$result) {
            $this->_db->rollback();
            return false;
        }
        $this->_db->commit();
        return 1;
    }

    protected function _commitOneNew(Sabai_Model_Gateway $gateway, Sabai_Model_Entity $entity)
    {
        if (!$insert_id = $gateway->insert($entity->getVars())) {
            $this->_setError($gateway->getError(), self::COMMIT_ERROR_NEW, $entity);

            return false;
        }
        $entity->set('id', $insert_id, false);
        $this->_commitNewEntityAssign($entity);
        $entity->setTempId(false);

        return true;
    }

    protected function _commitOneDirty(Sabai_Model_Gateway $gateway, Sabai_Model_Entity $entity)
    {
        if (!$gateway->updateById($entity->id, $entity->getVars())) {
            $this->_setError($gateway->getError(), self::COMMIT_ERROR_DIRTY, $entity);

            return false;
        }

        return true;
    }

    protected function _commitOneRemoved(Sabai_Model_Gateway $gateway, Sabai_Model_Entity $entity)
    {
        if (!$gateway->deleteById($entity->id, $entity->getVars())) {
            $this->_setError($gateway->getError(), self::COMMIT_ERROR_REMOVED, $entity);

            return false;
        }

        return true;
    }

    /**
     * Gets a commit error message string
     *
     * @return string
     */
    public function getCommitError()
    {
        return $this->_commitError;
    }

    /**
     * Gets a type of error occurred on commit
     *
     * @return int
     */
    public function getCommitErrorType()
    {
        return $this->_commitErrorType;
    }

    /**
     * Gets an instance of Sabai_Model_Entity that produced error on commit
     *
     * @return Sabai_Model_Entity
     */
    public function getCommitErrorEntity()
    {
        return $this->_commitErrorEntity;
    }

    /**
     * Commits new entities to the datasource
     *
     * @return integer if success, false otherwise
     */
    protected function _commitNew()
    {
        $count = 0;
        if (!empty($this->_entities['new'])) {
            foreach (array_keys($this->_entities['new']) as $name) {
                foreach (array_keys($this->_entities['new'][$name]) as $id) {
                    $entity = $this->_entities['new'][$name][$id];

                    // Make sure that the foreign entities are already commited
                    if (!$this->_commitEntitiesToBeAssigned($entity)) return false;

                    if (!$this->_commitOneNew($this->getGateway($name), $entity)) {
                        unset($entity, $this->_entities['new'][$name][$id]);
                        return false;
                    }
                    ++$count;
                }
            }
        }
        return $count;
    }

    /**
     * Commits modified entities to the datasource
     *
     * @return integer if success, false otherwise
     */
    protected function _commitDirty()
    {
        $count = 0;
        if (!empty($this->_entities['dirty'])) {
            foreach (array_keys($this->_entities['dirty']) as $name) {
                $gateway = $this->getGateway($name);
                foreach (array_keys($this->_entities['dirty'][$name]) as $id) {
                    $entity = $this->_entities['dirty'][$name][$id];

                    // Make sure that the foreign entities are already commited
                    if (!$this->_commitEntitiesToBeAssigned($entity)) return false;

                    if (!$this->_commitOneDirty($gateway, $entity)) {
                        unset($entity, $this->_entities['dirty'][$name][$id]);
                        return false;
                    }
                    ++$count;
                }
            }
        }
        return $count;
    }

    /**
     * Commits deleted entities to the datasource
     *
     * @return mixed integer if success, false otherwise
     */
    protected function _commitRemoved()
    {
        $count = 0;
        if (!empty($this->_entities['removed'])) {
            foreach (array_keys($this->_entities['removed']) as $name) {
                $gateway = $this->getGateway($name);
                if ($this->_db->isTriggerEnabled()) {
                    // do batch deletion if trigger is handled by the database
                    $criteria = $this->createCriteria($name);
                    if (false === $gateway->deleteByCriteria($criteria->id_in(array_keys($this->_entities['removed'][$name])))) {
                        $this->_setError($gateway->getError(), self::COMMIT_ERROR_REMOVED, $this->_entities['removed'][$name][0]);
                        unset($this->_entities['removed'][$name]);
                        return false;
                    }
                    $count = $count + count($this->_entities['removed'][$name]);
                } else {
                    foreach (array_keys($this->_entities['removed'][$name]) as $id) {
                        if (!$this->_commitOneRemoved($gateway, $this->_entities['removed'][$name][$id])) {
                            unset($this->_entities['removed'][$name][$id]);
                            return false;
                        }
                        ++$count;
                    }
                }
            }
        }
        return $count;
    }

    /**
     * Commits new entities that are to be assigned so that foreign keys are set properly
     *
     * @param Sabai_Model_Entity $entity
     * @return bool;
     */
    protected function _commitEntitiesToBeAssigned(Sabai_Model_Entity $entity)
    {
        $entities_to_be_assigned = $entity->getEntitiesToBeAssigned();
        foreach (array_keys($entities_to_be_assigned) as $entity_name) {
            foreach (array_keys($entities_to_be_assigned[$entity_name]) as $entity_fk) {
                $entity_temp_id = $entities_to_be_assigned[$entity_name][$entity_fk];
                // Commit the entity if not already committed
                if ($entity_to_be_assigned = @$this->_entities['new'][$entity_name][$entity_temp_id]) {
                    if (!$this->_commitOneNew($this->getGateway($entity_name), $entity_to_be_assigned)) {
                        unset($entity_to_be_assigned, $this->_entities['new'][$entity_name][$entity_temp_id]);
                        return false;
                    }
                    unset($entity_to_be_assigned, $this->_entities['new'][$entity_name][$entity_temp_id]);
                }
            }
        }
        return true;
    }

    /**
     * Assigns an entity to entities that reference this entity so that the new ID
     * is propagated properly to the referencing entities
     *
     * @param Sabai_Model_Entity $entity
     */
    protected function _commitNewEntityAssign(Sabai_Model_Entity $entity)
    {
        if ($entities_to_assign = $entity->getEntitiesToAssign()) {
            $entity_name = $entity->getName();
            foreach (array_keys($entities_to_assign) as $i) {
                if ($entities_to_assign[$i]->getName() == $entity_name) {
                    $method = 'assignParent';
                } else {
                    $method = 'assign' . $entity_name;
                }
                $entities_to_assign[$i]->$method($entity);
            }
            $entity->clearEntitiesToAssign();
        }
    }

    /**
     * Sets details of commit error
     *
     * @param string $error
     * @param int $type
     * @param Sabai_Model_Entity $entity
     */
    protected function _setError($error, $type, Sabai_Model_Entity $entity)
    {
        $this->_commitError = $error;
        $this->_commitErrorType = $type;
        $this->_commitErrorEntity = $entity;
    }
}
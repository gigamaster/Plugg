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
 * @subpackage EntityCollection
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

require_once 'Sabai/Model/EntityCollection/Decorator.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Model
 * @subpackage EntityCollection
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_Model_EntityCollection_Decorator_ForeignEntities extends Sabai_Model_EntityCollection_Decorator
{
    protected $_foreignSelfKey;
    protected $_foreignEntityName;
    protected $_foreignEntities;
    protected $_selfForeignVar;

    public function __construct($foreignSelfKey, $foreignEntityName, Sabai_Model_EntityCollection $collection, $selfForeignVar = null)
    {
        parent::__construct($collection);
        $this->_foreignSelfKey = $foreignSelfKey;
        $this->_foreignEntityName = $foreignEntityName;
        $this->_selfForeignVar = $selfForeignVar;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_foreignEntities)) {
            $this->_foreignEntities = array();
            if ($this->_collection->count() > 0) {
                $ids = !isset($this->_selfForeignVar) ? $this->_collection->getAllIds() : $this->_collection->getAllVars($this->_selfForeignVar);
                $criteria = Sabai_Model_Criteria::createIn($this->_foreignSelfKey, $ids);
                $entities = $this->_model->getRepository($this->_foreignEntityName)->fetchByCriteria($criteria);
                $foreign_var = substr($this->_foreignSelfKey, strpos($this->_foreignSelfKey, '_') + 1);
                foreach ($entities as $entity) {
                    $this->_foreignEntities[$entity->$foreign_var][] = $entity;
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $id = $current->id;
        $entities = !empty($this->_foreignEntities[$id]) ? $this->_foreignEntities[$id] : array();
        $current->setObject($this->_foreignEntityName, $this->_model->createCollection($this->_foreignEntityName, $entities));

        return $current;
    }
}
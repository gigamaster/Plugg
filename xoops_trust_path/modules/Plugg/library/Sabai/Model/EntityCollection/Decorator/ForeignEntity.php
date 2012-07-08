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
class Sabai_Model_EntityCollection_Decorator_ForeignEntity extends Sabai_Model_EntityCollection_Decorator
{
    protected $_foreignKeyVar;
    protected $_foreignEntityName;
    protected $_foreignEntityPrimaryKey;
    protected $_foreignEntities;
    protected $_foreitnEntityObjectVarName;
    protected $_criteria;
    private $_externalModel;

    public function __construct($foreignKeyVar, $foreignEntityName, $foreignEntityPrimaryKey, Sabai_Model_EntityCollection $collection, $foreignEntityObjectVarName = null, $criteria = null)
    {
        parent::__construct($collection);
        $this->_foreignKeyVar = $foreignKeyVar;
        $this->_foreignEntityName = $foreignEntityName;
        $this->_foreignEntityPrimaryKey = $foreignEntityPrimaryKey;
        $this->_foreitnEntityObjectVarName = isset($foreignEntityObjectVarName) ? $foreignEntityObjectVarName : $foreignEntityName;
        $this->_criteria = $criteria;
    }

    public function setExternalModel(Sabai_Model $model)
    {
        $this->_externalModel = $model;
    }

    protected function _getModel()
    {
        return isset($this->_externalModel) ? $this->_externalModel : $this->_model;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_foreignEntities)) {
            $this->_foreignEntities = array();
            if ($this->_collection->count() > 0) {
                while ($this->_collection->valid()) {
                    if ($foreign_id = $this->_collection->current()->{$this->_foreignKeyVar}) {
                        $foreign_ids[$foreign_id] = true;
                    }
                    $this->_collection->next();
                }
                if (!empty($foreign_ids)) {
                    if (!isset($this->_criteria)) {
                        $criteria = Sabai_Model_Criteria::createIn($this->_foreignEntityPrimaryKey, array_keys($foreign_ids));
                    } else {
                        $criteria = Sabai_Model_Criteria::createComposite(array($this->_criteria));
                        $criteria->addAnd(Sabai_Model_Criteria::createIn($this->_foreignEntityPrimaryKey, array_keys($foreign_ids)));
                    }
                    $this->_foreignEntities = $this->_getModel()->getRepository($this->_foreignEntityName)
                        ->fetchByCriteria($criteria)
                        ->getArray();
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $foreign_id = $current->{$this->_foreignKeyVar};
        if (isset($this->_foreignEntities[$foreign_id])) {
            $current->setObject($this->_foreitnEntityObjectVarName, $this->_foreignEntities[$foreign_id]);
        } else {
            $current->setObject($this->_foreitnEntityObjectVarName, false);
        }

        return $current;
    }
}
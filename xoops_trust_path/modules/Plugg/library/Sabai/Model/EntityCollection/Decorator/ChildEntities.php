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
class Sabai_Model_EntityCollection_Decorator_ChildEntities extends Sabai_Model_EntityCollection_Decorator
{
    protected $_parentKey;
    protected $_entityName;
    protected $_childEntities;

    public function __construct($entityName, $parentKey, Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
        $this->_parentKey = $parentKey;
        $this->_entityName = $entityName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_childEntities)) {
            $this->_childEntities = array();
            if ($this->_collection->count() > 0) {
                $criteria = Sabai_Model_Criteria::createIn($this->_parentKey, $this->_collection->getAllIds());
                $children = $this->_model->getRepository($this->_entityName)->fetchByCriteria($criteria);
                foreach ($children as $child) {
                    $this->_childEntities[$child->parent][] = $child;
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $id = $current->id;
        $entities = !empty($this->_childEntities[$id]) ? $this->_childEntities[$id] : array();
        $current->setObject('Children', $this->getModel()->createCollection($this->_entityName, $entities));

        return $current;
    }
}
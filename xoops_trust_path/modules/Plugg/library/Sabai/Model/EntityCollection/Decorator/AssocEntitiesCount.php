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
class Sabai_Model_EntityCollection_Decorator_AssocEntitiesCount extends Sabai_Model_EntityCollection_Decorator
{
    protected $_linkEntityName;
    protected $_linkSelfKey;
    protected $_assocEntityName;
    protected $_assocEntitiesCount;

    public function __construct($linkEntityName, $linkSelfKey, $assocEntityName, Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
        $this->_linkEntityName = $linkEntityName;
        $this->_linkSelfKey = $linkSelfKey;
        $this->_assocEntityName = $assocEntityName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_assocEntitiesCount)) {
            $this->_assocEntitiesCount = array();
            if ($this->_collection->count() > 0) {
                $criteria = Sabai_Model_Criteria::createIn($this->_linkSelfKey, $this->_collection->getAllIds());
                $fields = array($this->_linkSelfKey, 'COUNT(*)');
                if ($rs = $this->_model->getGateway($this->_linkEntityName)->selectByCriteria($criteria, $fields, 0, 0, null, null, $this->_linkSelfKey)) {
                    while ($row = $rs->fetchRow()) {
                        $this->_assocEntitiesCount[$row[0]] = $row[1];
                    }
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $count = isset($this->_assocEntitiesCount[$current->id]) ? $this->_assocEntitiesCount[$current->id] : 0;
        $current->setCount($this->_assocEntityName, $count);

        return $current;
    }
}
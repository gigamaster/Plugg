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
 * @subpackage PageCollection
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

require_once 'Sabai/Model/PageCollection.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Model
 * @subpackage PageCollection
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_Model_PageCollection_Entity extends Sabai_Model_PageCollection
{
    protected $_entityName;
    protected $_entityId;

    public function __construct($repository, $entityName, $entityId, $perpage, $sort, $order)
    {
        parent::__construct($repository, $perpage, $sort, $order);
        $this->_entityName = $entityName;
        $this->_entityId = $entityId;
    }

    protected function _getElementCount()
    {
        $method = 'countBy' . $this->_entityName;
        return $this->_repository->$method($this->_entityId);
    }
    
    protected function _getElements($limit, $offset)
    {
        $method = 'fetchBy' . $this->_entityName;
        return $this->_repository->$method($this->_entityId, $limit, $offset, $this->_sort, $this->_order);
    }
}
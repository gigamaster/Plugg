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
class Sabai_Model_PageCollection_Criteria extends Sabai_Model_PageCollection
{
    protected $_criteria;

    public function __construct(Sabai_Model_EntityRepository $repository, Sabai_Model_Criteria $criteria, $perpage, $sort, $order)
    {
        parent::__construct($repository, $perpage, $sort, $order);
        $this->_criteria = $criteria;
    }

    protected function _getElementCount()
    {
        return $this->_repository->countByCriteria($this->_criteria);
    }

    protected function _getElements($limit, $offset)
    {
        return $this->_repository->fetchByCriteria($this->_criteria, $limit, $offset, $this->_sort, $this->_order);
    }
}
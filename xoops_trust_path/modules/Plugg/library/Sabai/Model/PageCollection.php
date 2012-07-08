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

require_once 'Sabai/Page/Collection.php';

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
abstract class Sabai_Model_PageCollection extends Sabai_Page_Collection
{
    protected $_repository;
    protected $_sort;
    protected $_order;

    public function __construct(Sabai_Model_EntityRepository $repository, $perpage, $sort, $order, $key = 0)
    {
        parent::__construct($perpage, $key);
        $this->_repository = $repository;
        $this->_sort = $sort;
        $this->_order = $order;
    }
    
    protected function _getEmptyElements()
    {
        return $this->_repository->createCollection();
    }
}
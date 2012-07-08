<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_DB
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
 * @package    Sabai_DB
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
abstract class Sabai_DB_Rowset implements IteratorAggregate, Countable
{
    protected $_rs;

    const FETCH_MODE_NUM = 1;
    const FETCH_MODE_ASSOC = 1;

    /**
     * Constructor
     *
     */
    public function __construct($rs)
    {
        $this->_rs = $rs;
    }

    /**
     * @return Sabai_Model_GatewayRecordsetIterator
     */
    public function getIterator()
    {
        require_once 'Sabai/DB/RowsetIterator.php';
        return new Sabai_DB_RowsetIterator($this);
    }

    /**
     * Implementation of the Countable interface
     *
     * @return int
     */
    public function count()
    {
        return $this->rowCount();
    }

    /**
     * @param int $index
     * @return string
     */
    abstract public function fetchColumn($index = 0);
    /**
     * @param int $index
     * @return array
     */
    abstract public function fetchAllColumns($index = 0);
    /**
     * @return string
     */
    abstract public function fetchSingle();
    /**
     * @return array
     */
    abstract public function fetchAssoc();
    /**
     * @return array
     */
    abstract public function fetchRow();
    /**
     * @return array
     */
    abstract public function fetchAll($mode = Sabai_DB_Rowset::FETCH_MODE_ASSOC);
    /**
     * @param int $rowNum
     * @return bool
     */
    abstract public function seek($rowNum = 0);
    /**
     * @return int
     */
    abstract public function columnCount();
    /**
     * @return int
     */
    abstract public function rowCount();
}
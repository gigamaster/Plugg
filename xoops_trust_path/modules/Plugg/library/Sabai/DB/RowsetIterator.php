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
class Sabai_DB_RowsetIterator implements Iterator, Countable
{
    protected $_rs;
    protected $_key;

    public function __construct(Sabai_DB_Rowset $rs)
    {
        $this->_rs = $rs;
        $this->_key = 0;
    }

    public function rewind()
    {
        $this->_key = 0;
    }

    public function valid()
    {
        return $this->_rs->seek($this->_key);
    }

    public function next()
    {
        ++$this->_key;
    }

    public function current()
    {
        return $this->_rs->fetchAssoc();
    }

    public function key()
    {
        return $this->_key;
    }
    
    function count()
    {
        return $this->_rs->rowCount();
    }
}
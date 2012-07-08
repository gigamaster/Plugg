<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Page
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

require_once 'Sabai/Page.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Page
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
abstract class Sabai_Page_Collection implements Iterator, Countable
{
    protected $_perpage;
    protected $_key;
    private $_elementCount;

    public function __construct($perpage, $key = 0)
    {
        $this->_perpage = intval($perpage) ? $perpage : 10;
        $this->_key = $key;
    }

    /**
     * Gets the number of items per page
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->_perpage;
    }

    /**
     * Gets a valid page. Returns the 1st page if the requested page does not exist.
     * Returns an empty page if no page exists.
     *
     * @param int $pageNum
     * @return Sabai_Page_Page
     */
    public function getValidPage($pageNum)
    {
        $page_num = ($pageNum == 1 || !$this->hasPage($pageNum)) ? 1 : $pageNum;
        return $this->getPage($page_num);
    }

    public function hasPage($pageNum)
    {
        if (0 >= $page_num = intval($pageNum)) return false;

        $count = $this->getElementCount();
        return $count > ($page_num - 1) * $this->_perpage;
    }

    public function getPage($pageNum)
    {
        $offset = ($pageNum - 1) * $this->_perpage;
        if ($count = $this->getElementCount()) {
            $limit = ($count < $offset + $this->_perpage) ? $count - $offset : $this->_perpage;
        } else {
            $limit = 0;
        }
        return new Sabai_Page($this, $pageNum, $limit, $offset);
    }

    public function getElements($limit, $offset)
    {
        if (empty($limit)) {
            return $this->_getEmptyElements();
        }
        return $this->_getElements($limit, $offset);
    }

    protected function _getEmptyElements()
    {
        return new ArrayObject(array());
    }

    public function getElementCount($recount = false)
    {
        if (!isset($this->_elementCount) || $recount) {
            $this->_elementCount = $this->_getElementCount();
        }
        return $this->_elementCount;
    }

    public function getPageCount($recount = false)
    {
        return ceil($this->getElementCount($recount) / $this->_perpage);
    }

    public function count()
    {
        return $this->getPageCount();
    }

    public function rewind()
    {
        $this->_key = 0;
    }

    public function valid()
    {
        return $this->hasPage($this->key() + 1);
    }

    public function next()
    {
        ++$this->_key;
    }

    public function current(){
        return $this->getPage($this->key() + 1);
    }

    public function key()
    {
        return $this->_key;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return Traversable
     */
    protected abstract function _getElements($limit, $offset);

    /**
     * @return int
     */
    protected abstract function _getElementCount();
}
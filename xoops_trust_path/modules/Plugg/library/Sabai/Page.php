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
 * @since      File available since Release 0.1.10
*/

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
 * @since      Class available since Release 0.1.10
 */
final class Sabai_Page
{
    private $_pages;
    private $_pageNumber;
    private $_limit;
    private $_offset;

    public function __construct(Sabai_Page_Collection $pages, $pageNumber, $limit = 0, $offset = 0)
    {
        $this->_pages = $pages;
        $this->_pageNumber = $pageNumber;
        $this->_limit = $limit;
        $this->_offset = $offset;
    }

    public function getPageNumber()
    {
        return $this->_pageNumber;
    }

    public function getLimit()
    {
        return $this->_limit;
    }

    public function getOffset()
    {
        return $this->_offset;
    }

    public function getElements()
    {
        return $this->_pages->getElements($this->_limit, $this->_offset);
    }
}
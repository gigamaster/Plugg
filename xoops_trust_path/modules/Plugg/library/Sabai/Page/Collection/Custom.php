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

require_once 'Sabai/Page/Collection.php';

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
class Sabai_Page_Collection_Custom extends Sabai_Page_Collection
{
    protected $_getElementCountFunc;
    protected $_getElementsFunc;
    protected $_extraParams;
    protected $_extraParamsPrepend;

    public function __construct($getElementCountFunc, $getElementsFunc, $perpage, $extraParams = array(), $extraParamsPrepend = array(), $key = 0)
    {
        parent::__construct($perpage, $key);
        $this->_getElementCountFunc = $getElementCountFunc;
        $this->_getElementsFunc = $getElementsFunc;
        $this->_extraParams = $extraParams;
        $this->_extraParamsPrepend = $extraParamsPrepend;
    }

    protected function _getElementCount()
    {
        $params = array_merge($this->_extraParamsPrepend, $this->_extraParams);
        return call_user_func_array($this->_getElementCountFunc, $params);
    }
    
    protected function _getElements($limit, $offset)
    {
        $params = array_merge($this->_extraParamsPrepend, array($limit, $offset), $this->_extraParams);
        return call_user_func_array($this->_getElementsFunc, $params);
    }
}
<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Handle
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

/**
 * Sabai_Handle
 */
require_once 'Sabai/Handle.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Handle
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_Handle_FactoryMethod extends Sabai_Handle
{
    /**
     * @var mixed
     */
    protected $_factoryFunc;
    /**
     * @var array
     */
    protected $_params;

    /**
     * Constructor
     *
     * @param mixed $factoryFunc
     * @param array $params
     */
    public function __construct($factoryFunc, array $params = array())
    {
        $this->_factoryFunc = $factoryFunc;
        $this->_params = $params;
    }

    /**
     * Creates an instance
     *
     * @return object
     */
    public function instantiate()
    {
        return call_user_func_array($this->_factoryFunc, $this->_params);
    }
}
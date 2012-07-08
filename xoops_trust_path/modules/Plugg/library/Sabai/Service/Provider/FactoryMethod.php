<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Service
 * @copyright  Copyright (c) 2008 myWeb Japan (http://www.myweb.ne.jp/)
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
 * @package    Sabai_Service
 * @copyright  Copyright (c) 2008 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      FactoryMethod available since Release 0.1.10
 */
class Sabai_Service_Provider_FactoryMethod extends Sabai_Service_Provider
{
    protected $_factoryMethod;
    protected $_file;

    /**
     * Constructor
     *
     * @param mixed $factoryMethod string or array
     * @param array $params
     * @param string $file
     * @return Sabai_Service_Provider_FactoryMethod
     */
    public function __construct($factoryMethod, array $params = array(), $file = null)
    {
        $this->_defaultParams = $params;
        $this->_factoryMethod = $factoryMethod;
        $this->_file = $file;
    }

    protected function _doGetService(array $params)
    {
        require_once 'Sabai/Handle/FactoryMethod.php';
        $handle = new Sabai_Handle_FactoryMethod($this->_factoryMethod, array_values($params));
        if (isset($this->_file)) {
            require_once 'Sabai/Handle/Decorator/Autoload.php';
            $handle = new Sabai_Handle_Decorator_Autoload($handle, $this->_file);
        }
        return $handle->instantiate();
    }
}
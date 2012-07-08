<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Application
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.8
*/

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Application
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.8
 */
class Plugg_RoutingControllerRoute implements Sabai_Application_RoutingControllerRoute, ArrayAccess
{
    /**
     * @var string
     */
    private $_route;
    /**
     * @var string
     */
    private $_routeSelected;
    /**
     * @var array
     */
    private $_data;

    /**
     * Constructor
     *
     * @param array $routes
     * @param string $controllerRegex
     * @return Sabai_Application_RoutingControllerRouter
     */
    public function __construct($route, $routeSelected, array $data)
    {
        $this->_route = $route;
        $this->_routeSelected = $routeSelected;
        $this->_data = $data;
    }

    public function __toString()
    {
        return $this->_route;
    }

    /**
     * Gets the name of requested controller found in route
     *
     * @return string
     */
    function getController()
    {
        return isset($this->_data['controller']) ? $this->_data['controller'] : '';
    }

    /**
     * Returns controller file path
     *
     * @return string
     */
    function getControllerFile()
    {
        return isset($this->_data['controller_file']) ? $this->_data['controller_file'] : '';
    }

    /**
     * Returns controller constructor paramters
     *
     * @return array
     */
    function getControllerArgs()
    {
        return isset($this->_data['controller_args']) ? $this->_data['controller_args'] : array();
    }

    /**
     * Returns another route to which request should be fowarded
     *
     * @return mixed string or false
     */
    function isForward()
    {
        return isset($this->_data['forward']) ? $this->_data['forward'] : false;
    }

    /**
     * Gets extra parameter values
     *
     * @return array
     */
    function getParams()
    {
        return isset($this->_data['params']) ? $this->_data['params'] : array();
    }

    /**
     * Returns the route selected for the request
     *
     * @return string
     */
    function getRouteSelected()
    {
        return $this->_routeSelected;
    }

    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->_data[$offset];
    }
}
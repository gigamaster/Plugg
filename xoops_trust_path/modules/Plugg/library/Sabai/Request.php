<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Request
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
 * @package    Sabai_Request
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
abstract class Sabai_Request
{
    /**
     * @var array
     */
    protected $_params;
    
    /**
     * Constructor
     * @param array $params
     */
    protected function __construct(array $params)
    {
        $this->_params = $params;
    }
    
    /**
     * Returns all request parameters
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }
    
    /**
     * Gets a request variable as a certain PHP type variable
     *
     * @access protected
     * @param string $type
     * @param string $name
     * @param mixed $default
     * @param array $include
     * @param array $exclude
     * @return mixed
     */
    protected function _as($type, $name, $default, $include = array(), $exclude = array())
    {
        $ret = $default;
        if ($this->has($name)) {
            $ret = $this->get($name);
            settype($ret, $type);
            if (!empty($exclude)) {
                if (in_array($ret, $exclude)) {
                    $ret = $default;
                }
            } elseif (!empty($include)) {
                if (!in_array($ret, $include)) {
                    $ret = $default;
                }
            }
        }

        return $ret;
    }

    /**
     * Gets a certain request variable as array
     *
     * @param string $name
     * @param array $default
     * @param array $include
     * @param array $exclude
     * @return array
     */
    public function asArray($name, $default = array(), $include = array(), $exclude = array())
    {
        return $this->_as('array', $name, $default, $include, $exclude);
    }

    /**
     * Gets a certain request variable as string
     *
     * @param string $name
     * @param string $default
     * @param mixed $include
     * @param mixed $exclude
     * @return string
     */
    public function asStr($name, $default = '', $include = null, $exclude = null)
    {
        return $this->_as('string', $name, $default, (array)$include, (array)$exclude);
    }

    /**
     * Gets a certain request variable as integer
     *
     * @param string $name
     * @param int $default
     * @param mixed $include
     * @param mixed $exclude
     * @return int
     */
    public function asInt($name, $default = 0, $include = null, $exclude = null)
    {
        return $this->_as('integer', $name, $default, (array)$include, (array)$exclude);
    }

    /**
     * Gets a certain request variable as bool
     *
     * @param string $name
     * @param bool $default
     * @return bool
     */
    public function asBool($name, $default = false)
    {
        return $this->_as('boolean', $name, $default);
    }

    /**
     * Gets a certain request variable as float
     *
     * @param string $name
     * @param float $default
     * @param mixed $include
     * @param mixed $exclude
     * @return float
     */
    public function asFloat($name, $default = 0.0, $include = null, $exclude = null)
    {
        return $this->_as('float', $name, $default, (array)$include, (array)$exclude);
    }

    /**
     * Checks if a request parameter is present
     *
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->_params);
    }

    /**
     * Gets the value of a request parameter
     *
     * @return mixed
     * @param string $name
     */
    public function get($name)
    {
        return $this->_params[$name];
    }

    /**
     * Sets the value of a request parameter
     *
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value)
    {
        $this->_params[$name] = $value;
    }
}
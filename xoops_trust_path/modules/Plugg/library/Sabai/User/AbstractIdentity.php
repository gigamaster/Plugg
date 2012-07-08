<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_User
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.7
*/

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_User
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.7
 */
abstract class Sabai_User_AbstractIdentity
{
    protected $_id, $_vars = array();
    private $_data, $_dataLoader;
 

    /**
     * Constructor
     *
     * @param string $id
     * @param array $vars Extra identity parameters
     * @return Sabai_User_AbstractIdentity
     */
    protected function __construct($id, array $vars)
    {
        $this->_id = $id;
        $this->_vars = $vars;
    }

    /**
     * Magic method
     *
     * @param string $key
     */
    public function __get($key)
    {
        switch ($key) {
            case 'id':
                return $this->_id;
            default:
                return $this->_vars[$key];
        }
    }

    /**
     * Magic method
     *
     * @param string $key
     * @param mixed
     */
    public function __set($key, $value)
    {
        $this->_vars[$key] = $value;
    }

    /**
     * Prevent extra data from being serialized
     */
    public function __sleep()
    {
        return array('_id', '_vars');
    }

    /**
     * Sets an extra profile data
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * .
     * Gets an extra data. Pass in more parameters to narrow the search.
     *
     * @param string $key
     * @return mixed
     */
    public function getData($key = null)
    {
        $this->loadData(); // lazy loading
        if (!isset($key)) {
            return $this->_data;
        }
        $data = $this->_data[$key];
        if (func_num_args() > 1) {
            $names = array_slice(func_get_args(), 1);
            foreach ($names as $name) {
                if (is_array($data) && array_key_exists($name, $data)) {
                    $data = $data[$name];
                } else {
                    trigger_error(sprintf('Request to non-existent key "%s"', $name), E_USER_NOTICE);
                    $data = null;
                    break;
                }
            }
        }
        return $data;
    }

    /**
     * .
     * Checks is an extra data exists. Pass in more parameters to narrow the search.
     *
     * @param string $key
     * @return mixed
     */
    public function hasData($key)
    {
        $this->loadData(); // lazy loading
        if (!array_key_exists($key, $this->_data)) return false;

        $data = $this->_data[$key];
        if (func_num_args() > 1) {
            $names = array_slice(func_get_args(), 1);
            foreach ($names as $name) {
                if (is_array($data) && array_key_exists($name, $data)) {
                    $data = $data[$name];
                } else {
                    return false;
                }
            }
        }
        return $data;
    }

    /**
     * Loads extra user data using a callback
     *
     */
    public function loadData()
    {
        if ($this->isDataLoaded()) return; // already loaded once
        
        $this->_data = array();
        
        if (!isset($this->_dataLoader)) return;

        if (is_callable($this->_dataLoader)) {
            call_user_func_array($this->_dataLoader, array($this));
        }
        unset($this->_dataLoader);
    }

    /**
     * Sets a callback for loading extra user data
     *
     * @param mixed $callback a valid callback function string or array
     */
    public function setDataLoader($callback)
    {
        $this->_dataLoader = $callback;
    }
    
    /**
     * Checks whether user identity data has been loaded once
     * 
     * @return bool
     */
    public function isDataLoaded()
    {    
        return isset($this->_data);
    }

    abstract public function isAnonymous();
}
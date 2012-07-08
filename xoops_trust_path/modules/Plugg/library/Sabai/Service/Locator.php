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

require_once 'Sabai/Service/Provider.php';

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
 * @since      Class available since Release 0.1.10
 */
class Sabai_Service_Locator
{
    /**
     * @var array
     */
    protected $_providers = array();
    /**
     * @var array
     */
    protected $_services = array();

    /**
     * Gets a service object
     *
     * @param string $name
     * @param string $id
     * @param array $params
     * @return object
     */
    public function getService($name, $id = 'default', array $params = array())
    {
        if (!isset($this->_services[$name][$id])) {
            // do not allow passing custom params to the default
            $params = ($id == 'default') ? array() : $params;
            $this->_services[$name][$id] = $this->createService($name, $params);
        }
        return $this->_services[$name][$id];
    }

    /**
     * Gets a service object
     *
     * @param string $name
     * @param array $params
     * @return object
     */
    public function createService($name, array $params = array())
    {
        return $this->_providers[$name]->getService($this, $params);
    }

    /**
     * Checks if a service is available via a provider
     *
     * @param string $name
     * @return bool
     */
    public function isService($name, $id = null)
    {
        return is_null($id) ? isset($this->_providers[$name]) : isset($this->_providers[$name][$id]);
    }

    /**
     * Clears a specific service
     *
     * @param string $name
     * @param string $id
     */
    public function clearService($name, $id = null)
    {
        if (isset($id)) {
            unset($this->_services[$name][$id]);
        } else {
            unset($this->_services[$name]);
        }
    }

    /**
     * Adds a service provider
     *
     * @param string $name
     * @param Sabai_Service_Provider $provider
     */
    public function addProvider($name, Sabai_Service_Provider $provider)
    {
        $this->_providers[$name] = $provider;
    }

    /**
     * Add a service provider
     *
     * @param string $name
     * @param array $params
     * @param string $class
     * @param mixed $file string or array
     */
    public function addProviderClass($name, array $params = array(), $class = null, $file = null)
    {
        require_once 'Sabai/Service/Provider/Class.php';
        $class = isset($class) ? $class : $name;
        $this->addProvider($name, new Sabai_Service_Provider_Class($class, $params, $file));
    }

    /**
     * Add a service provider
     *
     * @param string $name
     * @param mixed $factoryMethod string or array
     * @param array $params
     * @param mixed $file string or array
     */
    public function addProviderFactoryMethod($name, $factoryMethod, array $params = array(), $file = null)
    {
        require_once 'Sabai/Service/Provider/FactoryMethod.php';
        $this->addProvider($name, new Sabai_Service_Provider_FactoryMethod($factoryMethod, $params, $file));
    }

    /**
     * Accessing the public property will return the default instance of a service
     */
    public function __get($name) {
        return $this->getService($name);
    }

    /**
     * Returns the default parameter for a service
     *
     * @param string $name
     * @param string $key
     * @return mixed
     */
    public function getDefaultParam($name, $key = null)
    {
        $params = $this->_providers[$name]->getDefaultParams();
        return isset($key) ? $params[$key] : $params;
    }
}
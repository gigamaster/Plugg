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
 * @since      Class available since Release 0.1.10
 * @abstract
 */
abstract class Sabai_Service_Provider
{
    /**
     * @var array
     */
    protected $_defaultParams = array();

    /**
     * Gets an instance of this service
     *
     * @return object
     * @param Sabai_Service_Locator $locator
     * @param array $params
     */
    public function getService(Sabai_Service_Locator $locator, array $params = array())
    {
        $params = array_merge($this->_defaultParams, $params);
        foreach (array_keys($params) as $k) {
            if ($params[$k] instanceof stdclass &&
                $locator->isService($k) &&
                array_key_exists($k, $this->_defaultParams)
            ) {
                if (empty($params[$k]->params) || empty($params[$k]->id)) {
                    // Use default when no params set
                    $service_params = array();
                    $service_id = 'default';
                } else {
                    $service_params = $params[$k]->params;
                    $service_id = $params[$k]->id;
                }
                $params[$k] = $locator->getService($k, $service_id, $service_params);
            }
        }
        return $this->_doGetService($params);
    }

    /**
     * Returns the default parameter for the service
     *
     * @return array
     */
    public function getDefaultParams()
    {
        return $this->_defaultParams;
    }

    /**
     * Gets an instance of this service
     *
     * @return object
     * @param array $params
     */
    abstract protected function _doGetService(array $params);
}
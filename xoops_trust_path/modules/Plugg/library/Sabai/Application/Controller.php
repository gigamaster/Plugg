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
 * @copyright  Copyright (c) 2008 myWeb Japan (http://www.myweb.ne.jp/)
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
 * @copyright  Copyright (c) 2008 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.8
 */
abstract class Sabai_Application_Controller
{
    /**
     * @var Sabai_Application
     */
    protected $_application;
    /**
     * @var array
     */
    private $_filters = array();
    /**
     * @var array
     */
    private $_activeFilters = array();
    /**
     * @var Sabai_Application_RoutingController
     */
    private $_parent;

    /**
     * Sets the parent controller
     *
     * @param Sabai_Application_RoutingController $controller
     */
    final public function setParent(Sabai_Application_RoutingController $controller)
    {
        $this->_parent = $controller;
    }

    /**
     * Gets the parent controller
     *
     * @return Sabai_Application_RoutingController $controller
     */
    final public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Adds a filter for all actions in the controller
     *
     * @param mixed $filter Sabai_Handle object or string
     */
    final public function addFilter($filter)
    {
        $this->_filters[] = $filter;
    }

    /**
     * Adds filters for all actions in the controller
     *
     * @param array $filters array of Sabai_Handle object or string
     */
    final public function addFilters(array $filters)
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * Adds a filter to the first index for all actions in the controller
     *
     * @param mixed $filter Sabai_Handle object or string
     */
    final public function prependFilter($filter)
    {
        array_unshift($this->_filters, $filter);
    }

    /**
     * Sets filters for all actions in the controller
     *
     * @param array $filters
     */
    final public function setFilters(array $filters)
    {
        $this->_filters = $filters;
    }

    /**
     * Executes the controller
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     */
    final public function execute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->_filterBefore($request, $response);
        $this->_doExecute($request, $response);
        $this->_filterAfter($request, $response);
    }

    /**
     * Executes the controller
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     */
    abstract protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response);

    /**
     * Executes pre-filters
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     */
    protected function _filterBefore(Sabai_Request $request, Sabai_Application_Response $response)
    {
        foreach (array_keys($this->_filters) as $i) {
            $this->_executeBeforeFilter($this->_filters[$i], $request, $response);

            // Add the filter to the active filters stack
            $this->_activeFilters[$i] = $this->_filters[$i];
        }
    }

    /**
     * Executes a before filter
     *
     * @param mixed $filter
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     */
    protected function _executeBeforeFilter($filter, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (is_object($filter)) {
            $filter->instantiate()->before($request, $response, $this->_application);
        } elseif (is_array($filter)) {
            call_user_func_array($filter, array('before', $request, $response, $this->_application));
        } else {
            $method = $filter . 'BeforeFilter';
            $this->$method($request, $response);
        }
    }

    /**
     * Executes after filters
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     */
    protected function _filterAfter(Sabai_Request $request, Sabai_Application_Response $response)
    {
        foreach (array_keys($this->_activeFilters) as $i) {
            $this->_executeAfterFilter($this->_activeFilters[$i], $request, $response);

            // Remove the filter from the active filters stack
            unset($this->_activeFilters[$i]);
        }
    }

    /**
     * Executes a before filter
     *
     * @param mixed $filter
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     */
    protected function _executeAfterFilter($filter, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (is_object($filter)) {
            $filter->instantiate()->after($request, $response, $this->_application);
        } elseif (is_array($filter)) {
            call_user_func_array($filter, array('after', $request, $response, $this->_application));
        } else {
            $method = $filter . 'AfterFilter';
            $this->$method($request, $response);
        }
    }

    final public function __call($method, $args)
    {
        return call_user_func_array(array($this->_application, $method), $args);
    }
    
    final public function __get($name)
    {
        return $this->_application->$name;
    }
    
    final public function __set($name, $value)
    {
        is_object($this->_application) || $this->_application = new stdClass();
        $this->_application->$name = $value;
    }
    
    final public function __isset($name)
    {
        return isset($this->_application->$name);
    }
    
    final public function __unset($name)
    {
        unset($this->_application->$name);
    }

    /**
     * Sets an application instance
     *
     * @param Sabai_Application $application
     */
    final public function setApplication(Sabai_Application $application)
    {
        $this->_application = $application;
    }

    /**
     * Forwards to another route
     *
     * @param string $route
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     * @param bool $stackContentName
     */
    public function forward($route, Sabai_Request $request, Sabai_Application_Response $response, $stackContentName = false)
    {
        // Remove the global filters that have been activated by this controller
        $this->_activeFilters = array();

        $this->_parent->forward($route, $request, $response, $stackContentName);
    }
}
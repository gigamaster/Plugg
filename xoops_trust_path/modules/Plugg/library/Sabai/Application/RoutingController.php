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
 * Sabai_Application_Controller
 */
require_once 'Sabai/Application/Controller.php';

/**
 * Front Controller
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
abstract class Sabai_Application_RoutingController extends Sabai_Application_Controller
{
    /**
     * Class prefix for controllers
     *
     * @var string
     */
    protected $_controllerPrefix;
    /**
     * Path to directory where controller class files are located
     *
     * @var string
     */
    protected $_controllerDir;

    /**
     * Name of the default controller
     *
     * @var string
     */
    protected $_defaultController;
    /**
     * Arguments passed to the default controller if any
     *
     * @var array
     */
    protected $_defaultControllerArgs;
    /**
     * Path to the the default controller class file
     *
     * @var string
     */
    protected $_defaultControllerFile;

    /**
     * Constructor
     *
     * @param string $controllerPrefix
     * @param string $controllerDir
     * @param string $defaultController
     * @param array $defaultControllerArgs
     * @param string $defaultControllerFile
     * @return Sabai_Application_RoutingController
     */
    protected function __construct($controllerPrefix, $controllerDir, $defaultController, array $defaultControllerArgs = array(), $defaultControllerFile = null)
    {
        $this->_controllerPrefix = $controllerPrefix;
        $this->_controllerDir = $controllerDir;
        $this->_defaultController = $defaultController;
        $this->_defaultControllerArgs = $defaultControllerArgs;
        $this->_defaultControllerFile = $defaultControllerFile;
    }

    /**
     * Runs the controller
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     */
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($route = $this->_isRoutable($request, $response)) {
            if ($forward = $route->isForward()) { // forward to another route?
                // Re-route to the forwarded route
                $this->_doForward($forward, $request, $response);
            } else {
                $this->_executeRoutable($request, $response, $route);
            }
        } else {
            if ($response->isError() || $response->isSuccess()) return;

            $this->_executeDefault($request, $response);
        }
    }

    /**
     * Forwards request to another route
     *
     * @param string $route
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     * @param bool $stackContentName
     */
    public function forward($route, Sabai_Request $request, Sabai_Application_Response $response, $stackContentName = false)
    {
        Sabai_Log::info(sprintf('Forwarding request to route "%s" by %s', $route, get_class($this)), __FILE__, __LINE__);

        //Remove previous matched route
        $this->popRoutesMatched();

        $this->_doForward($route, $request, $response);
    }

    /**
     * Fowards request to another route
     *
     * @param string $route
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     */
    protected function _doForward($route, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->setNextRoute($route);
        if ($new_route = $this->_isRoutable($request, $response)) {
            // Is this route forwarded to another route?
            if ($forward = $new_route->isForward()) {
                // Recursive forwarding is not allowed
                throw new Exception(
                    sprintf('Recursive request forwarding detected. The request forwarded to route %s may not be forwarded to another route %s.', $this->getNextRoute(), $forward)
                );
            } else {
                $this->_executeRoutable($request, $response, $new_route);
            }
        } else {
            if ($response->isError() || $response->isSuccess()) return;

            if ($parent = $this->getParent()) {
                // Remove the global filters that have been activated by this controller
                $this->_activeFilters = array();

                $parent->forward($route, $request, $response);

                return;
            }

            $this->_executeDefault($request, $response);
        }
    }

    /**
     * Runs the controller if any
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     * @param Sabai_Application_RoutingControllerRoute $route
     */
    protected function _executeRoutable(Sabai_Request $request, Sabai_Application_Response $response, Sabai_Application_RoutingControllerRoute $route)
    {
        // Update context for executing the controller
        $this->_updateRoutableContext($request, $response, $route);

        $this->_doExecuteController(
            $request,
            $response,
            $route->getController(),
            $route->getControllerArgs(),
            $route->getControllerFile()
        );
    }

    /**
     * Updates context for the routable controller
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     * @param Sabai_Application_RoutingControllerRoute $route
     */
    protected function _updateRoutableContext(Sabai_Request $request, Sabai_Application_Response $response, Sabai_Application_RoutingControllerRoute $route)
    {
        // Set request parameters if any
        foreach ($route->getParams() as $key => $value) {
            $request->set($key, $value);
        }

        // Add matched route
        $route_matched = (string)$route;
        $this->pushRoutesMatched($route_matched);
        // Set the next route
        if (!$next_route = substr($this->getNextRoute(), strlen($route_matched))) {
            $next_route = '';
        }
        $this->setNextRoute($next_route);
    }

    /**
     * Executes the default controller
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     */
    protected function _executeDefault(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->_updateDefaultContext($request, $response);

        $this->_doExecuteController(
            $request,
            $response,
            $this->_defaultController,
            $this->_defaultControllerArgs,
            $this->_defaultControllerFile
        );
    }

    /**
     * Updates context for the default controller
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     * @param Sabai_Application_RoutingControllerRouter $router
     */
    protected function _updateDefaultContext(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Add empty string as matched route
        $this->pushRoutesMatched('');
    }

    /**
     * Runs the controller if any
     *
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     * @param string $controllerName
     * @param array $controllerArgs
     * @param string $controllerFile
     */
    protected function _doExecuteController(Sabai_Request $request, Sabai_Application_Response $response, $controllerName, array $controllerArgs = array(), $controllerFile = null)
    {
        if (!empty($controllerFile)) {
            $controller_class = $controllerName;
            $controller_file = $controllerFile;
        } else {
            $controller_class = $this->_controllerPrefix . $controllerName;
            $controller_file = $this->_controllerDir . '/' . $controllerName . '.php';
        }

        if ($controller = $this->_getControllerInstance($controllerName, $controller_file, $controller_class, $controllerArgs)) {
            Sabai_Log::info(sprintf('Executing controller %s(%s)', $controller_class, $controller_file), __FILE__, __LINE__);
            $controller->execute($request, $response);
            Sabai_Log::info(sprintf('Controller %s(%s) executed', $controllerName, $controller_class), __FILE__, __LINE__);
        }
    }

    protected function _getControllerInstance($controllerName, $controllerFile, $controllerClass, array $controllerArgs)
    {
        if (!file_exists($controllerFile)) return false;

        require_once $controllerFile;

        if (!empty($controllerArgs)) {
            $reflection = new ReflectionClass($controllerClass);
            $controller = $reflection->newInstanceArgs($controllerArgs);
        } else {
            $controller = new $controllerClass();
        }
        $controller->setParent($this);
        $controller->setApplication($this->_application);

        return $controller;
    }

    /**
     * Returns a Sabai_Application_RoutingControllerRouter instance
     *
     * @return mixed Sabai_Application_RoutingControllerRoute or false
     * @param Sabai_Request $request
     * @param Sabai_Application_Response $response
     */
    abstract protected function _isRoutable(Sabai_Request $request, Sabai_Application_Response $response);
}
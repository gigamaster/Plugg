<?php
require_once 'Sabai.php';
require_once 'Sabai/Application/Helper.php';

class Sabai_Application
{
    protected $_id, $_name, $_title, $_path, $_routeParam, $_data = array(),
        $_helpers = array(), $_helperDir = array(), $_helperPrefix = '';

    private $_nextRoute, $_routesMatched = array();

    /**
     * Constructor
     */
    protected function __construct($id, $name, $title, $path, $routeParam)
    {
        $this->_id = $id;
        $this->_name = $name;
        $this->_title = $title;
        $this->_path = $path;
        $this->_routeParam = $routeParam;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getName()
    {
        return $this->_name;
    }
    
    public function getTitle()
    {
        return $this->_title;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getRouteParam()
    {
        return $this->_routeParam;
    }

    public function run(Sabai_Application_Controller $controller, Sabai_Request $request, Sabai_Application_Response $response = null)
    {
        if (!isset($response)) $response = new Sabai_Application_Response();
        $this->_doRun($controller, $request, $response);
        
        return $response;
    }

    protected function _doRun(Sabai_Application_Controller $controller, Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Initial route is fetched from the request
        $this->setNextRoute($request->asStr($this->_routeParam));
        $response->setApplication($this);
        $controller->setApplication($this);
        $controller->execute($request, $response);
    }

    public function getNextRoute()
    {
        return $this->_nextRoute;
    }

    public function setNextRoute($route)
    {
        $this->_nextRoute = $route;
        return $this;
    }

    public function pushRoutesMatched($route)
    {
       $this->_routesMatched[] = $route;
       return $this;
    }

    public function popRoutesMatched()
    {
        return array_pop($this->_routesMatched);
    }

    public function getRoutesMatched()
    {
        return $this->_routesMatched;
    }
    
    public function getRequestedRoute()
    {
        return implode('', $this->_routesMatched);
    }

    public function setSessionVar($name, $value)
    {
        $_SESSION[$this->_id][$name] = $value;
    }

    public function getSessionVar($name)
    {
        return $_SESSION[$this->_id][$name];
    }

    public function hasSessionVar($name)
    {
        return !empty($_SESSION[$this->_id]) && array_key_exists($name, $_SESSION[$this->_id]);
    }

    public function unsetSessionVar($name)
    {
        unset($_SESSION[$this->_id][$name]);
    }

    public function clearSession()
    {
        $_SESSION[$this->_id] = array();
    }

    public function getHelper($name)
    {
        if (!isset($this->_helpers[$name])) {
            if (!$this->_loadHelper($name)) {
                throw new Exception(sprintf('Call to undefined application helper %s', $name));
            }
        } elseif ($this->_helpers[$name] instanceof Sabai_Handle) {
            $this->_helpers[$name] = $this->_helpers[$name]->instantiate();
        }

        return $this->_helpers[$name];
    }

    protected function _loadHelper($name)
    {
        $class = $this->_helperPrefix . $name;
        foreach (array_keys($this->_helperDir) as $i) {
            foreach ($this->_helperDir[$i] as $helper_dir) {
                $class_path = sprintf('%s/%s.php', $helper_dir, $name);
                if (file_exists($class_path)) {
                    require $class_path;
                    $this->setHelper($name, new $class());
                    
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Set an application helper
     * @param $name string
     * @param $helper Sabai_Application_Helper
     */
    public function setHelper($name, $helper)
    {
        $this->_helpers[$name] = $helper;

        return $this;
    }

    public function addHelperDir($dir, $priority = 0)
    {
        if (!isset($this->_helperDir[$priority])) {
            $this->_helperDir[$priority] = array($dir);
        } else {
            array_unshift($this->_helperDir[$priority], $dir);
        }
        // Sort by priority
        krsort($this->_helperDir, SORT_NUMERIC);
    }

    /**
     * Call a helper method with the application object prepended to the arguments
     */
    public function __call($name, $args)
    {
        array_unshift($args, $this);

        return call_user_func_array(array($this->getHelper($name), 'help'), $args);
    }
    
    public function getData()
    {
        return $this->_data;
    }

    public function setData($data, $value = null)
    {
        if (is_array($data)) {
            $this->_data = array_merge($this->_data, $data);
        } else {
            $this->_data[$data] = $value;
        }
        
        return $this;
    }
    
    /**
     * PHP magic __get() method
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->_data[$name];
    }
    
    /**
     * PHP magic method
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }
    
    /**
     * PHP magic method
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * PHP magic method
     *
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->_data[$name]);
    }
}
<?php
abstract class Sabai_Application_View
{
    /**
     * @var Sabai_Application
     */
    private $_application;
    
    /**
     * Constructor
     * @param $application Sabai_Application
     */
    public function __construct(Sabai_Application $application)
    {
        $this->_application = $application;
    }
    
    /**
     * Calls an application helper
     * @param string $name
     * @param array $args
     */
    public function __call($name, $args)
    {   
        array_unshift($args, $this->_application);

        return call_user_func_array(array($this->_application->getHelper($name), 'help'), $args);
    }

    /**
     * PHP magic method
     *
     * @param string $name
     * @return bool
     */
    abstract public function __isset($name);

    /**
     * PHP magic method
     *
     * @param string $name
     */
    abstract public function __unset($name);

    /**
     * PHP magic method
     *
     * @param string $name
     * @param mixed $value
     */
    abstract public function __set($name, $value);
    
    /**
     * Renders the view
     * @return string
     */
    abstract public function render();
    
    /**
     * Manual assignment of template variables, or ability to assign
     * multiple variables en masse.
     * @param mixed $name string or array
     * @param mixed $value
     * @return Sabai_Application_View
     */
    abstract public function assign($name, $value = null);
}
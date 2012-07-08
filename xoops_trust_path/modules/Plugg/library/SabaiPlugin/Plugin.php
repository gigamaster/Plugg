<?php
require_once 'Sabai/Event/Dispatchable.php';

abstract class SabaiPlugin_Plugin implements Sabai_Event_Dispatchable
{
    /**
     * @var string
     * @access protected
     */
    protected $_name;
    /**
     * @var string
     * @access protected
     */
    protected $_path;
    /**
     * @var string
     * @access protected
     */
    protected $_version;

    protected function __construct($name, $path, $version)
    {
        $this->_name = $name;
        $this->_path = $path;
        $this->_version = $version;
    }

    final public function dispatchEvent(Sabai_Event $event)
    {
        $method = 'on' . $event->getType();
        return call_user_func_array(array($this, $method), $event->getVars());
    }
    
    public function __get($name)
    {
        $property = '_' . $name;

        return $this->$property;
    }

    final public function __set($name, $value)
    {
        throw new Exception(sprintf('Property %s may not be set using the magic __set() method', $name));
    }
}
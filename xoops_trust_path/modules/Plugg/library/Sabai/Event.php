<?php
class Sabai_Event
{
    private $_type;
    private $_vars;

    public function __construct($type, array $vars = array())
    {
        $this->_type = $type;
        $this->_vars = $vars;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setVar($key, $value)
    {
        $this->_vars[$key] = $value;
    }

    public function setVars($vars)
    {
        $this->_vars = array_merge($this->_vars, $vars);
    }

    public function getVars()
    {
        return $this->_vars;
    }
}
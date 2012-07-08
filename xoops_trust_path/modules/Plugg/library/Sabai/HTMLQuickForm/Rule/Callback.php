<?php
require_once 'HTML/QuickForm/Rule/Callback.php';

class Sabai_HTMLQuickForm_Rule_Callback extends HTML_QuickForm_Rule_Callback
{
    function validate($value, $options = array())
    {
        if (isset($this->_data[$this->name])) {
            $callback = $this->_data[$this->name];
            if (is_callable($callback)) {
                $params = array_merge(array($value), (array)$options);
                return call_user_func_array($callback, $params);
            }
        } elseif (is_callable($options)) {
            return call_user_func($options, $value);
        }
        return true;
    }
    
    function addData($name, $callback, $class = null)
    {
        $this->_data[$name] = !empty($class) ? array($class, $callback) : $callback;
    }
}
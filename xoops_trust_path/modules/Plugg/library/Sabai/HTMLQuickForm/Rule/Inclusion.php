<?php
require_once 'HTML/QuickForm/Rule.php';

class Sabai_HTMLQuickForm_Rule_Inclusion extends HTML_QuickForm_Rule
{
    /**
     * Checks if the value is included in a list of allowed values
     *
     * @param mixed $value
     * @param array $options
     * @return bool
     */
    function validate($value, $options = array())
    {
        if (empty($options)) {
            return true;
        }
        return in_array($value, $options);
    }
}
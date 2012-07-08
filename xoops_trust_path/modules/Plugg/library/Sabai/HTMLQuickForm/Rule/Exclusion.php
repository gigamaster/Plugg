<?php
require_once 'HTML/QuickForm/Rule.php';

class Sabai_HTMLQuickForm_Rule_Exclusion extends HTML_QuickForm_Rule
{
    /**
     * Checks if the value is NOT included in a supplied list
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
        return !in_array($value, $options);
    }
}
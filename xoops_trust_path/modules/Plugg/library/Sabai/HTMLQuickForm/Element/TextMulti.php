<?php
require_once 'HTML/QuickForm/textarea.php';

class Sabai_HTMLQuickForm_Element_TextMulti extends HTML_QuickForm_textarea
{
    var $_separator = PHP_EOL;
    var $_keyValueSeparator = null;
    var $_submitValueModified = false;

    function Sabai_HTMLQuickForm_Element_TextMulti($elementName = null, $elementLabel = null, $attributes = null)
    {
        parent::HTML_QuickForm_textarea($elementName, $elementLabel, $attributes);
    }

    function setSeparator($separator)
    {
        $this->_separator = $separator;
    }

    function setKeyValueSeparator($separator)
    {
        $this->_keyValueSeparator = $separator;
    }

    /**
     * Overrides the default to convert submitted string value to an array
     *
     * @param     string    $event  Name of event
     * @param     mixed     $arg    event arguments
     * @param     object    &$caller calling object
     * @return    void
     */
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'updateValue':
                // constant values override both default and submitted ones
                // default values are overriden by submitted
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    $value = $this->_findValue($caller->_submitValues);
                    if (null !== $value) {
                        // Convert submitted value that comes as text string to an array
                        // so that Sabai_HTMLQuickForm::getSubmitValue() returns an array
                        if (!$this->_submitValueModified) {
                            $value = trim($value, $this->_separator);
                            if (strlen($value)) {
                                $value_arr = explode($this->_separator, $value);
                                if (!empty($value_arr) && isset($this->_keyValueSeparator)) {
                                    $new_value_arr = array();
                                    foreach ($value_arr as $_value) {
                                        $_value_arr = explode($this->_keyValueSeparator, $_value);
                                        $new_value_arr[trim($_value_arr[0])] = isset($_value_arr[1]) ? $_value_arr[1] : $_value_arr[0];
                                    }
                                    $value_arr = $new_value_arr;
                                }
                                $value_arr = array_map('trim', $value_arr);
                            } else {
                                $value_arr = array();
                            }
                            $name = $this->getName();
                            if (isset($caller->_submitValues[$name])) {
                                $caller->_submitValues[$name] = $value_arr;
                            } elseif (strpos($name, '[')) {
                                $myVar = "['" . str_replace(
                                    array('\\', '\'', ']', '['), array('\\\\', '\\\'', '', "']['"), $name
                                ) . "']";
                                eval("\$caller->_submitValues$myVar = \$value_arr;");
                            }
                            $this->_submitValueModified = true;
                            $value = $value_arr;
                        }
                    } else {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if ($value !== null && !empty($value)) {
                    if (isset($this->_keyValueSeparator)) {
                        $new_value = array();
                        foreach ($value as $k => $v) {
                            $new_value[] = $k . $this->_keyValueSeparator . $v;
                        }
                        $value = $new_value;
                    }
                    $value = implode($this->_separator, $value);
                    $this->setValue($value);
                }
                break;
            default:
                parent::onQuickFormEvent($event, $arg, $caller);
        }

        return true;
    }
}
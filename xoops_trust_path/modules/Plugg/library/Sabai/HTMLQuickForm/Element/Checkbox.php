<?php
require_once 'HTML/QuickForm/checkbox.php';

class Sabai_HTMLQuickForm_Element_Checkbox extends HTML_QuickForm_checkbox
{
    var $_checkedHtml = '<tt>[x]</tt>';
    var $_uncheckedHtml = '<tt>[ ]</tt>';
    
    function Sabai_HTMLQuickForm_Element_Checkbox($elementName = null, $elementLabel = null, $text = '', $attributes = null)
    {
        parent::HTML_QuickForm_checkbox($elementName, $elementLabel, $text, $attributes);
    }

    /*
     * Overrides the parent method to cope with the bug below
     * http://pear.php.net/bugs/bug.php?id=15298
     */
    function getValue()
    {
        return $this->getAttribute('value');
    }
    
    function getFrozenHtml()
    {
        if ($this->getChecked()) {
            return $this->_checkedHtml . $this->_getPersistantData();
        } else {
            return $this->_uncheckedHtml;
        }
    }
}
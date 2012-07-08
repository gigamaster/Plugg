<?php
require_once 'HTML/QuickForm/file.php';

class Sabai_HTMLQuickForm_Element_File extends HTML_QuickForm_file
{
    protected $_multiple = false;
    
    public function Sabai_HTMLQuickForm_Element_File($elementName = null, $elementLabel = null, $attributes = null)
    {
        parent::HTML_QuickForm_file($elementName, $elementLabel, $attributes);
    }
    
    public function setMultiple($flag = true)
    {
        $this->_multiple = (bool)$flag;
    }

    /**
     * Overrides the parent class so that file values will be included
     * in exported values
     * 
     * @param $submitValues
     * @param $assoc
     */
    function exportValue(&$submitValues, $assoc = false)
    {
        $value = $this->_findValue($submitValues);
        if (null === $value) {
            $value = $this->getValue();
        }
        
        return $this->_prepareValue($value, $assoc);
    }
    
    function toHtml()
    {
        if ($this->_flagFrozen) return $this->getFrozenHtml();
        
        if (!$this->_multiple) {
            $attr = $this->_getAttrString($this->_attributes);
        } else {
            $name = $this->getName();
            $this->setName($name . '[]');
            $attr = $this->_getAttrString($this->_attributes);
            $this->setName($name);
        }
        
        return $this->_getTabs() . '<input' . $attr . ' />';
    }
}
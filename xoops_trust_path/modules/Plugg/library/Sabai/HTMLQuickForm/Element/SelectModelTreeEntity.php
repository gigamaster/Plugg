<?php
require_once 'HTML/QuickForm/select.php';

class Sabai_HTMLQuickForm_Element_SelectModelTreeEntity extends HTML_QuickForm_select
{
    var $_model;
    var $_entityName;
    var $_prefix;

    function __construct($elementName = null, $elementLabel = null, $options = null, $attributes = null)
    {
        parent::HTML_QuickForm_select($elementName, $elementLabel, $options, $attributes);
    }

    /**
     * PHP4 style constructor required for compat with the HTMLQuickForm library
     */
    function Sabai_HTMLQuickForm_Element_SelectModelTreeEntity($elementName = null, $elementLabel = null, $options = null, $attributes = null)
    {
        $this->__construct($elementName, $elementLabel, $options, $attributes);
    }

    function setModel($model)
    {
        $this->_model = $model;
        return $this;
    }

    function setEntityName($entityName)
    {
        $this->_entityName = $entityName;
        return $this;
    }

    function setPrefix($prefix)
    {
        $this->_prefix = $prefix;
        return $this;
    }

    function accept($renderer)
    {
        // option for no parent
        $this->addOption('', 0);
        $entities = array();
        foreach ($this->_model->getRepository($this->_entityName)->fetch() as $option) {
            $entities[$option->parent][] = $option;
        }
        $prefix = !isset($this->_prefix) ? ' - ' : $this->_prefix;
        if (!empty($entities[0])) {
            foreach (array_keys($entities[0]) as $i) {
                $this->_fillTreeEntityOption($entities, $entities[0][$i], $prefix);
            }
        }
        parent::accept($renderer);
    }

    function _fillTreeEntityOption($entities, $entity, $prefix)
    {
        $id = $entity->id;
        $this->addOption($prefix . (string)$entity, $id);
        if (!empty($entities[$id])) {
            foreach (array_keys($entities[$id]) as $i) {
                $this->_fillTreeEntityOption($entities, $entities[$id][$i], $prefix . $this->_prefix);
            }
        }
    }
}
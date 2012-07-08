<?php
require_once 'HTML/QuickForm/select.php';

class Sabai_HTMLQuickForm_Element_SelectModelEntity extends HTML_QuickForm_select
{
    var $_model;
    var $_entityName;
    var $_paginate = false;
    var $_currentPage;
    var $_pageUrl;
    var $_perpage;
    var $_sort;
    var $_order;

    function __construct($elementName = null, $elementLabel = null, $options = null, $attributes = null)
    {
        parent::HTML_QuickForm_select($elementName, $elementLabel, $options, $attributes);
    }

    /**
     * PHP4 style constructor required for compat with the HTMLQuickForm library
     */
    function Sabai_HTMLQuickForm_Element_SelectModelEntity($elementName = null, $elementLabel = null, $options = null, $attributes = null)
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

    function paginate($currentPage, $pageUrl, $perpage, $sort = null, $order = null)
    {
        $this->_paginate = true;
        $this->_currentPage = $currentPage;
        $this->_pageUrl = $pageUrl;
        $this->_perpage = $perpage;
        $this->_sort = $sort;
        $this->_order = $order;
    }

    function accept($renderer)
    {
        $repository = $this->_model->getRepository($this->_entityName);
        if ($this->_paginate) {
            $options = $repository->paginate($this->_perpage, $this->_sort, $this->_order)
                ->getValidPage($this->_currentPage)
                ->getElements();
        } else {
            $options = $repository->fetch();
        }
        foreach ($options as $option) {
            $this->addOption((string)$option, $option->id);
            unset($option);
        }
        parent::accept($renderer);
    }
}
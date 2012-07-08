<?php
require_once 'HTML/QuickForm/ElementGrid.php';

class Sabai_HTMLQuickForm_Element_Grid extends HTML_QuickForm_ElementGrid
{
    var $_footer;
    var $_emptyText;
    var $_rowAttributes;
    var $_columnAttributes;
    var $_footerAttributes;
    
    function Sabai_HTMLQuickForm_Element_Grid($name = null, $label = null, $options = null)
    {
        parent::HTML_QuickForm_ElementGrid($name, $label, $options);
    }
    
    function addColumnName($columnName, array $columnAttributes = null)
    {
        $this->_columnNames[] = $columnName;
        $this->_columnAttributes[] = $columnAttributes;
    }

    /**
     * Sets the rows
     *
     * @param array array of HTML_QuickForm elements
     */
    function setRows($rows)
    {
        foreach (array_keys($rows) as $key) {
            $this->addRow($rows[$key]);
        }
    }

    /**
     * Adds a row to the grid
     *
     * @param array array of HTML_QuickForm elements
     */
    function addRow($row, $attributes = null)
    {
        $key = sizeof($this->_rows);
        $this->_rows[$key] = $row;

        //if updateValue has been called make sure to update the values of each added element
        foreach (array_keys($this->_rows[$key]) as $key2) {
            if (isset($this->_form)) {
                $this->_rows[$key][$key2]->onQuickFormEvent('updateValue', null, $this->_form);
            }
            if ($this->isFrozen()) {
                $this->_rows[$key][$key2]->freeze();
            }
        }
        
        if (isset($attributes)) $this->_rowAttributes[$key] = $attributes;
    }

    /**
     * Returns Html for the element
     *
     * @access      public
     * @return      string
     */
    function toHtml()
    {
        require_once 'HTML/Table.php';
        $table = new HTML_Table(null, 0, true);
        $table->updateAttributes($this->getAttributes());

        $tbody = $table->getBody();
        $tbody->setAutoGrow(true);
        $tbody->setAutoFill('');

        $thead = $table->getHeader();
        $thead->setAutoGrow(true);
        $thead->setAutoFill('');
        
        $tfoot = $table->getFooter();
        $tfoot->setAutoGrow(true);
        $tfoot->setAutoFill('');
        
        if (isset($this->_footer) && is_array($this->_footer) && count($this->_footer)) {
            $footer = $this->_footer;
        } elseif (is_string($this->_footer)) {
            $last_column = array_pop(array_keys($this->_columnNames));
            $footer = array($last_column => $this->_footer);
            if (isset($this->_footerAttributes['@all'])) {
                if (isset($this->_footerAttributes[$last_column])) {
                    $this->_footerAttributes[$last_column] = array_merge($this->_footerAttributes['@all'], $this->_footerAttributes[$last_column]);
                } else {
                    $this->_footerAttributes[$last_column] = $this->_footerAttributes['@all'];
                }
                unset($this->_footerAttributes['@all']);
            }
        }

        $col = 0;
        if ($this->_columnNames) {
            foreach ($this->_columnNames as $key => $value) {
                $thead->setHeaderContents(0, $col, $value, $this->_columnAttributes[$key]);
                if (isset($footer)) {
                    if (isset($footer[$key])) {
                        $attributes = isset($this->_footerAttributes['@all']) ? $this->_footerAttributes['@all'] : array();
                        if (isset($this->_footerAttributes[$key])) $attributes = array_merge($attributes, $this->_footerAttributes[$key]);
                        if (isset($col_spanned)) {
                            $tfoot->setCellContents(0, $col_spanned, $footer[$key]);
                            $tfoot->setCellAttributes(0, $col_spanned, array_merge($attributes, array('colspan' => $key - $col_spanned + 1)));
                            unset($col_spanned);
                        } else {
                            $tfoot->setCellContents(0, $col, $footer[$key]);
                            if (!empty($attributes)) $tfoot->setCellAttributes(0, $col, $attributes);
                        }
                    } else {
                        if (!isset($col_spanned)) $col_spanned = $key;
                    }
                }
                ++$col;
            }
        }
        if (!empty($this->_rows)) {
            $row = 0;
            foreach (array_keys($this->_rows) as $key) {
                $col = 0;
                foreach (array_keys($this->_rows[$key]) as $key2) {
                    $tbody->setCellContents($row, $col, $this->_rows[$key][$key2]->toHTML());
                    $attributes = isset($this->_rowAttributes[$key]['@all']) ? $this->_rowAttributes[$key]['@all'] : array();
                    if (isset($this->_rowAttributes[$key][$key2])) $attributes = array_merge($attributes, $this->_rowAttributes[$key][$key2]);
                    if (!empty($attributes)) $tbody->setCellAttributes($row, $col, $attributes); 
                    ++$col;
                }
                if (isset($this->_rowAttributes[$key]['@row'])) $tbody->setRowAttributes($row, $this->_rowAttributes[$key]['@row'], true);
                ++$row;
            }
        } elseif (isset($this->_emptyText)) {
            $tbody->setCellContents(0, 0, $this->_emptyText);
            $tbody->setCellAttributes(0, 0, array('align' => 'center', 'colspan' => count($this->_columnNames)));
        }

        return $table->toHTML();
    }
    
    function setFooter($footer, array $footerAttributes = null)
    {
        $this->_footer = $footer;
        $this->_footerAttributes = $footerAttributes;
    }
    
    function setEmptyText($emptyText)
    {
        $this->_emptyText = $emptyText;
    }
}
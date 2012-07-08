<?php
require_once 'MDB2/Schema/Parser.php';

class Sabai_DB_SchemaParser extends MDB2_Schema_Parser
{
    var $_options;

    function Sabai_DB_SchemaParser($variables, $fail_on_invalid_names = true, $structure = false, $valid_types = array(), $force_defaults = true, $options = array())
    {
        parent::MDB2_Schema_Parser($variables, $fail_on_invalid_names, $structure, $valid_types, $force_defaults);
        $this->_options = array_merge(array('table_prefix' => '', 'database_name' => ''), $options);
    }

    function cdataHandler($xp, $data)
    {
        switch ($this->element) {
        case 'database-name':
            $data = $this->_options['database_name'];
            break;
        case 'database-table-initialization-insert-select-table':
        case 'database-table-name':
        case 'database-table-was':
        case 'database-table-declaration-foreign-references-table':
        case 'database-sequence-on-table':
            $data = $this->_options['table_prefix'] . $data;
            break;
        default:
            break;
        }
        parent::cdataHandler($xp, $data);
    }
}
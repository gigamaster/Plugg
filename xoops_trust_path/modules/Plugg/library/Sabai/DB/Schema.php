<?php
require_once 'Sabai/DB/MDB2Schema.php';
require_once 'Sabai/DB/SchemaParser.php';

class Sabai_DB_Schema
{
    /**
     * @var MDB2_Schema
     */
    protected $_mdb2Schema;
    /**
     * @var array
     */
    protected $_createTableOptions;
    /**
     * @var array
     */
    private $_errors;

    /**
     * Constructor
     *
     * @param MDB2_Schema $mdb2Schema
     * @param array $createTableOptions
     * @return Sabai_DB_Schema
     */
    public function __construct(Sabai_DB_MDB2Schema $mdb2Schema, array $createTableOptions = array())
    {
        $this->_mdb2Schema = $mdb2Schema;
        $this->_createTableOptions = $createTableOptions;
    }

    /**
     * Creates a Sabai_DB_Schema instance
     *
     * @param Sabai_DB $db
     * @param array $options
     * @param array $parserOptions
     * @param array $createTableOptions
     * @return mixed Sabai_DB_Schema on success, PEAR_Error on failure
     */
    public static function factory(Sabai_DB $db, array $options = array(), array $parserOptions = array(), array $createTableOptions = array())
    {
        $default = array(
            'log_line_break' => '<br />',
            'idxname_format' => '%s',
            'debug' => true,
            'quote_identifier' => true,
            'force_defaults' => false,
            'portability' => false,
            'parser' => 'Sabai_DB_SchemaParser',
            'use_transactions' => false, // ToDo: See why setting this option to true causes MDB2_SAVEPINT_2 not found error when schema has insert data
            'drop_missing_tables' => true
        );
        //$mdb2_schema =& MDB2_Schema::factory($db->getDSN(), array_merge($default, $options));
        $create_table_options = array_merge($db->getMDB2CreateTableOptions(), $createTableOptions);
        $schema_options = array(
            'create_table_options' => $create_table_options,
            'parser_options' => array_merge(array(
                'table_prefix'  => $db->getResourcePrefix(),
                'database_name' => $db->getResourceName()
            ), $parserOptions)
        );
        $mdb2_schema = Sabai_DB_MDB2Schema::factory($db->getDSN(), $schema_options, array_merge($default, $options));
        if (PEAR::isError($mdb2_schema)) {
            return $mdb2_schema;
        }
        return new Sabai_DB_Schema($mdb2_schema, $create_table_options);
    }

    public function create($schemaFile)
    {
        $definition = $this->_mdb2Schema->parseDatabaseDefinitionFile($schemaFile);
        if (PEAR::isError($definition)) {
            $this->_setError($definition);
            return false;
        }

        $result = $this->_mdb2Schema->createDatabase($definition, $this->_createTableOptions);
        if (PEAR::isError($result)) {
            $this->_setError($result);
            return false;
        }
        return true;
    }

    public function update($schemaFile, $previousSchemaFile)
    {
        $result = $this->_mdb2Schema->updateDatabase($schemaFile, $previousSchemaFile);
        if (PEAR::isError($result)) {
            $this->_setError($result);
            return false;
        }

        // Do execute insert/update/delete queries if any
        $definition = $this->_mdb2Schema->parseDatabaseDefinitionFile($schemaFile);
        if (PEAR::isError($definition)) {
            $this->_setError($definition);
        } else {
            foreach ($definition['tables'] as $table_name => $table) {
                if (empty($table['initialization'])) {
                    continue;
                }
                $result = $this->_mdb2Schema->initializeTable($table_name, $table);
                if (PEAR::isError($result)) {
                    $this->_setError($result);
                }
            }
        }

        return true;
    }

    public function drop($previousSchemaFile)
    {
        $changes = array();
        $definition = $this->_mdb2Schema->parseDatabaseDefinitionFile($previousSchemaFile);
        if (PEAR::isError($definition)) {
            $this->_setError($definition);
            return false;
        }
        foreach (array_keys($definition['tables']) as $table_name) {
            $changes['tables']['remove'][$table_name] = true;
        }
        foreach (array_keys($definition['sequences']) as $sequence_name) {
            $changes['sequences']['remove'][$sequence_name] = true;
        }
        $result = $this->_mdb2Schema->alterDatabase($definition, $definition, $changes);
        if (PEAR::isError($result)) {
            $this->_setError($result);
            return false;
        }
        return true;
    }

    protected function _setError($pearError)
    {
        $this->_errors[] = sprintf('%s(%s)', $pearError->getMessage(), $pearError->getUserInfo());
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}
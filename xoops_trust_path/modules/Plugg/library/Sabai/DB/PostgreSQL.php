<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_DB
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_DB
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_DB_PostgreSQL extends Sabai_DB
{
    private $_affectedRows;

    /**
     * Constructor
     *
     * @return Sabai_DB_PostgreSQL
     */
    public function __construct(Sabai_DB_Connection_PostgreSQL $connection)
    {
        parent::__construct($connection);
    }

    public function beginTransaction()
    {
        return pg_query($this->_resourceId, 'BEGIN');
    }

    public function commit()
    {
        return pg_query($this->_resourceId, 'COMMIT');
    }

    public function rollback()
    {
        return pg_query($this->_resourceId, 'ROLLBACK');
    }

    public function getQuery($sql, $limit = 0, $offset = 0)
    {
        if (intval($limit)) $sql .=  sprintf(' LIMIT %d OFFSET %d', $limit, $offset);

        return $sql;
    }

    protected function _doQuery($query)
    {
        if ($rs = pg_query($this->_resourceId, $query)) {
            Sabai_Log::info(sprintf('SQL "%s" executed', $query));
            if (!class_exists('Sabai_DB_Rowset_PostgreSQL', false)) require 'Sabai/DB/Rowset/PostgreSQL.php';

            return new Sabai_DB_Rowset_PostgreSQL($rs);
        }
        Sabai_Log::warn(sprintf('SQL "%s" failed. Error: "%s"', $query, $this->lastError()));

        return false;
    }

    public function exec($sql, $useAffectedRows = true)
    {
        if (!$result = pg_query($this->_resourceId, $sql)) return false;
        Sabai_Log::info(sprintf('SQL "%s" executed', $sql));
        $this->_affectedRows = pg_affected_rows($result);
        return $useAffectedRows ? $this->_affectedRows : true;
    }

    public function affectedRows()
    {
        return $this->_affectedRows;
    }

    public function lastInsertId($tableName, $keyName)
    {
        $sql = sprintf('SELECT last_value FROM %s_%s_seq', $tableName, $keyName);
        if (!$result = pg_query($this->_resourceId, $sql)) return false;
        if (!$row = pg_fetch_row($result)) return false;
        return $row[0];
    }

    public function lastError()
    {
        return pg_last_error($this->_resourceId);
    }

    public function escapeBool($value)
    {
        return intval($value);
    }

    public function escapeString($value)
    {
        return "'" . pg_escape_string($this->_resourceId, $value) . "'";
    }

    public function escapeBlob($value)
    {
        return "'" . pg_escape_bytea($this->_resourceId, $value) . "'";
    }
    
    protected function _doGetVersion()
    {
        $version = pg_version($this->_resourceId);
        
        return $version['server'];
    }
}

function sabai_db_unescapeBlob($value)
{
    return pg_unescape_bytea($value);
}
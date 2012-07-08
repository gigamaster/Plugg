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
class Sabai_DB_SQLite extends Sabai_DB
{
    protected $_resourceMode;

    /**
     * Constructor
     *
     * @return Sabai_DB_SQLite
     */
    public function __construct(array $config)
    {
        parent::__construct('SQLite');
        $this->_resourceMode = isset($config['mode']) ? $config['mode'] : 0666;
        $this->_resourceName = $config['dbname'];
    }

    public function connect()
    {
        if (false === $link = sqlite_open($this->_resourceName, $this->_resourceMode, $error)) {
            trigger_error(sprintf('Unable to connect to database server. ERROR: %s', $error), E_USER_WARNING);
            return false;
        }
        $this->_connection->getResourceId() = $link;
        return true;
    }

    public function beginTransaction()
    {
        return sqlite_exec($this->_connection->getResourceId(), 'BEGIN');
    }

    public function commit()
    {
        return sqlite_exec($this->_connection->getResourceId(), 'COMMIT');
    }

    public function rollback()
    {
        return sqlite_exec($this->_connection->getResourceId(), 'ROLLBACK');
    }

    public function getQuery($sql, $limit = 0, $offset = 0)
    {
        if (intval($limit)) $sql .=  sprintf(' OFFSET %d LIMIT %d', $offset, $limit);

        return $sql;
    }

    protected function _doQuery($query)
    {
        if ($rs = sqlite_query($this->_connection->getResourceId(), $query)) {
            if (!class_exists('Sabai_DB_Rowset_SQLite', false)) require 'Sabai/DB/Rowset/SQLite.php';

            return new Sabai_DB_Rowset_SQLite($rs);
        }

        return false;
    }

    public function exec($sql, $useAffectedRows = true)
    {
        if (!sqlite_exec($this->_connection->getResourceId(), $sql)) return false;
        return $useAffectedRows ? sqlite_changes($this->_connection->getResourceId()) : true;
    }

    public function affectedRows()
    {
        return sqlite_changes($this->_connection->getResourceId());
    }

    public function lastInsertId($tableName, $keyName)
    {
        if (!$id = sqlite_last_insert_rowid($this->_connection->getResourceId())) return false;
        return $id;
    }

    public function lastError()
    {
        $code = sqlite_last_error($this->_connection->getResourceId());
        return sprintf('%s(%s)', sqlite_error_string($code), $code);
    }

    public function escapeBool($value)
    {
        return intval($value);
    }

    public function escapeString($value)
    {
        return "'" . sqlite_escape_string($value) . "'";
    }

    public function getDSN()
    {
        return sprintf('sqlite:///%s?mode=%s', rawurlencode($this->_resourceName), rawurlencode($this->_resourceMode));
    }
    
    protected function _doGetVersion()
    {
        return sqlite_libversion($this->_connection->getResourceId());
    }
}

function sabai_db_unescapeBlob($value)
{
    return $value;
}
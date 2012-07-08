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

require_once 'Sabai/DB/MySQL.php';

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
class Sabai_DB_MySQLi extends Sabai_DB_MySQL
{

    public function __construct(Sabai_DB_Connection_MySQLi $connection)
    {
        parent::__construct($config);
    }

    public function beginTransaction()
    {
        return mysqli_autocommit($this->_connection->getResourceId(), false);
    }

    public function commit()
    {
        $ret = mysqli_commit($this->_connection->getResourceId());
        mysqli_autocommit($this->_connection->getResourceId(), true);
        return $ret;
    }

    public function rollback()
    {
        $ret = mysqli_rollback($this->_connection->getResourceId());
        mysqli_autocommit($this->_connection->getResourceId(), true);
        return $ret;
    }

    protected function _doQuery($query)
    {
        if ($rs = mysqli_query($query, $this->_connection->getResourceId())) {
            if (!class_exists('Sabai_DB_Rowset_MySQLi', false)) require 'Sabai/DB/Rowset/MySQLi.php';

            return new Sabai_DB_Rowset_MySQLi($rs);
        }

        return false;
    }

    public function exec($sql, $useAffectedRows = true)
    {
        if (!mysqli_query($sql, $this->_connection->getResourceId())) {
            Sabai_Log::warn(sprintf('SQL "%s" failed. Error: "%s"', $sql, $this->lastError()));
            return false;
        }
        Sabai_Log::info(sprintf('SQL "%s" executed', $sql));
        return $useAffectedRows ? mysqli_affected_rows($this->_connection->getResourceId()) : true;
    }

    public function affectedRows()
    {
        return mysqli_affected_rows($this->_connection->getResourceId());
    }

    public function lastInsertId($tableName, $keyName)
    {
        if (!$id = mysqli_insert_id($this->_connection->getResourceId())) return false;
        return $id;
    }

    public function lastError()
    {
        return sprintf('%s(%s)', mysqli_error($this->_connection->getResourceId()), mysqli_errno($this->_connection->getResourceId()));
    }

    /**
     * Escapes a string value for MySQL DB
     *
     * @param string $value
     * @return string
     */
    public function escapeString($value)
    {
        return "'" . mysqli_real_escape_string($this->_connection->getResourceId(), $value) . "'";
    }

    protected function _doGetVersion()
    {
        $version = mysqli_get_server_version($this->_connection->getResourceId());

        return  sprintf('%d.%d.%d', $version / 10000, ($version % 10000) / 100, $version % 100);
    }
}
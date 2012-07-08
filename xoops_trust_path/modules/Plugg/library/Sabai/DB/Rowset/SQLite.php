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
 * @subpackage Rowset
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

require_once 'Sabai/DB/Rowset.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_DB
 * @subpackage Rowset
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_DB_Rowset_SQLite extends Sabai_DB_Rowset
{
    public function fetchColumn($index = 0)
    {
        if ($row = sqlite_fetch_row($this->_rs)) {
            return $row[$index];
        }
        return '';
    }

    public function fetchAllColumns($index = 0)
    {
        $ret = array();
        while ($row = sqlite_fetch_row($this->_rs)) {
            $ret[] = $row[$index];
        }

        return $ret;
    }

    public function fetchSingle()
    {
        return sqlite_fetch_single($this->_rs);
    }

    public function fetchRow()
    {
        return sqlite_fetch_array($this->_rs, SQLITE_NUM);
    }

    public function fetchAssoc()
    {
        return sqlite_fetch_array($this->_rs, SQLITE_ASSOC);
    }

    public function fetchAll($mode = parent::FETCH_MODE_ASSOC)
    {
        return parent::FETCH_MODE_NUM == $mode ? sqlite_fecth_all(SQLITE_NUM) : sqlite_fetch_all(SQLITE_ASSOC);
    }

    public function seek($rowNum = 0)
    {
        sqlite_seek($rowNum);
    }

    public function columnCount()
    {
        return sqlite_num_fields($this->_rs);
    }

    public function rowCount()
    {
        return sqlite_num_rows($this->_rs);
    }
}
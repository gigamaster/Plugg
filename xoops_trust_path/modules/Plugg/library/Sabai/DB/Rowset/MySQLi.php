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
class Sabai_DB_Rowset_MySQLi extends Sabai_DB_Rowset
{
    public function fetchColumn($index = 0)
    {
        if ($row = mysqli_fetch_row($this->_rs)) {
            return $row[$index];
        }
        return '';
    }

    public function fetchAllColumns($index = 0)
    {
        $ret = array();
        while ($row = mysqli_fetch_row($this->_rs)) {
            $ret[] = $row[$index];
        }

        return $ret;
    }
    
    public function fetchSingle()
    {
        return $this->fetchColumn(0);
    }

    public function fetchRow()
    {
        return mysqli_fetch_row($this->_rs);
    }

    public function fetchAssoc()
    {
        return mysqli_fetch_assoc($this->_rs);
    }

    public function fetchAll($mode = Sabai_DB_Rowset::FETCH_MODE_ASSOC)
    {
        $ret = array();
        switch ($mode) {
            case Sabai_DB_Rowset::FETCH_MODE_NUM:
                $func = 'mysqli_fetch_row';
                break;
            default:
                $func = 'mysqli_fetch_assoc';
                break;
        }
        while ($row = $func($this->_rs)) {
            $ret[] = $row;
        }
        return $ret;
    }

    public function seek($rowNum = 0)
    {
        // mysqli_data_seek() returns null on success, false otherwise according to php.net
        return false !== mysqli_data_seek($this->_rs, $rowNum);
    }

    public function columnCount()
    {
        return mysqli_num_fields($this->_rs);
    }

    public function rowCount()
    {
        return mysqli_num_rows($this->_rs);
    }
}
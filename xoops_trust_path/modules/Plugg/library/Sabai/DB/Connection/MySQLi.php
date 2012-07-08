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

require_once 'Sabai/DB/Connection/MySQL.php';

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
class Sabai_DB_Connection_MySQLi extends Sabai_DB_Connection_MySQL
{
    protected $_resourcePort;
    
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->_resourcePort = isset($config['port']) ? intval($config['port']) : 3306;
    }
    
    public function connect()
    {
        $link = mysqli_init();    
        if (!mysqli_real_connect($link, $this->_resourceHost, $this->_resourceUser, $this->_resourceUserPassword, $this->_resourceName, $this->_resourcePort, null, $this->_clientFlags | MYSQLI_CLIENT_FOUND_ROWS)) {
            trigger_error(sprintf('Unable to connect to database server. Error: %s(%s)', mysqli_connect_error(), mysqli_connect_errno()), E_USER_WARNING);
            return false;
        }
        mysqli_autocommit($link, true);
        
        // Set client encoding if requested
        if (!empty($this->_clientEncoding)) {
            if ($mysql_charset = @self::$_charsets[strtolower($this->_clientEncoding)]) {
                if (!mysqli_set_charset($link, $mysql_charset)) $this->_clientEncoding = null;
            }
        }
        
        $this->_resourceId = $link;
        return true;
    }

    public function getDSN()
    {
        return sprintf('mysqli://%s:%s@%s:%d/%s',
            rawurlencode($this->_resourceUser),
            rawurlencode($this->_resourceUserPassword),
            rawurlencode($this->_resourceHost),
            $this->_resourcePort,
            rawurlencode($this->_resourceName)
        );
    }
}
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

// mysql_affeceted_rows() returns 0 if no data is modified
// even there was a match, not desirable for implementing
// the optimistic offline locking pattern in which we need
// to return false or 0 only when no matching record was found.
// We can change this behaviour of mysql by supplying the
// following constant to mysql_connect()
if (!defined('MYSQL_CLIENT_FOUND_ROWS')) {
    define('MYSQL_CLIENT_FOUND_ROWS', 2);
}

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
class Sabai_DB_Connection_MySQL extends Sabai_DB_Connection
{
    /**
     * @var string
     */
    protected $_resourceHost;
    /**
     * @var string
     */
    protected $_resourceUser;
    /**
     * @var string
     */
    protected $_resourceUserPassword;
    /**
     * @var int
     */
    protected $_clientFlags = 0;
    /**
     * @var string
     */
    protected $_clientEncoding;

    /**
     * @var array
     */
    protected static $_charsets = array(
        'utf-8' => 'utf8',
        'big5' => 'big5',
        'cp-866' => 'cp866',
        'euc-jp' => 'ujis',
        'euc-kr' => 'euckr',
        'gb2312' => 'gb2312',
        'gbk' => 'gbk',
        'iso-8859-1' => 'latin1',
        'iso-8859-2' => 'latin2',
        'iso-8859-7' => 'greek',
        'iso-8859-8' => 'hebrew',
        'iso-8859-8-i' => 'hebrew',
        'iso-8859-9' => 'latin5',
        'iso-8859-13' => 'latin7',
        'iso-8859-15' => 'latin1',
        'koi8-r' => 'koi8r',
        'shift_jis' => 'sjis',
        'tis-620' => 'tis620',
    );

    /**
     * Constructor
     *
     * @return Sabai_DB_Connection_MySQL
     */
    public function __construct(array $config)
    {
        parent::__construct('MySQL');
        $this->_resourceName = $config['dbname'];
        $this->_resourceHost = $config['host'];
        $this->_resourceUser = $config['user'];
        $this->_resourceUserPassword = $config['pass'];
        $this->_clientFlags = intval(@$config['clientFlags']);
        $this->_charset = @$config['charset'];
    }

    /**
     * Connects to the mysql server and DB
     *
     * @return bool
     */
    public function connect()
    {
        $link = mysql_connect($this->_resourceHost, $this->_resourceUser, $this->_resourceUserPassword, false, $this->_clientFlags | MYSQL_CLIENT_FOUND_ROWS);
        if ($link === false) {
            trigger_error(sprintf('Unable to connect to database server @%s', $this->_resourceHost), E_USER_WARNING);
            return false;
        }
        if (!mysql_select_db($this->_resourceName, $link)) {
            trigger_error(sprintf('Unable to connect to database %s', $this->_resourceName), E_USER_WARNING);
            return false;
        }

        // Set client encoding if requested
        if (!empty($this->_charset)
            && ($encoding = $this->_getClientEncoding($this->_charset))
        ) {
            if (function_exists('mysql_set_charset')) {
                $result = mysql_set_charset($encoding, $link);
            } else {
                $result = mysql_query('SET NAMES ' . $encoding, $link);
            }
            if ($result) $this->_clientEncoding = $encoding;
        }

        $this->_resourceId = $link;

        return true;
    }

    public function getDSN()
    {
        return sprintf('mysql://%s:%s@%s/%s',
            rawurlencode($this->_resourceUser),
            rawurlencode($this->_resourceUserPassword),
            rawurlencode($this->_resourceHost),
            rawurlencode($this->_resourceName)
        );
    }

    private function _getClientEncoding($charset)
    {
        // Return the original if no mapping is required
        if (in_array($charset, self::$_charsets)) return $charset;

        return @self::$_charsets[strtolower($charset)];
    }
}
<?php
require_once 'Sabai/Request.php';

class Sabai_Request_Http extends Sabai_Request
{
    protected $_cookie;

    /**
     * Constructor
     *
     * @return Sabai_Request_Web
     * @param bool $filterGlobals
     * @param bool $forceStripSlashes
     */
    public function __construct($filterGlobals = true, $forceStripSlashes = false)
    {
        $params = array_merge($_GET, $_POST);
        $this->_cookie = $_COOKIE;
        if ($filterGlobals) {
            if ($forceStripSlashes || get_magic_quotes_gpc()) {
                $params = self::_stripSlashes($params);
                $this->_cookie = self::_stripSlashes($this->_cookie);
            }
            // Filter malicious user inputs
            $list = array('GLOBALS', '_GET', '_POST', '_REQUEST', '_COOKIE', '_ENV', '_FILES', '_SERVER', '_SESSION');
            self::_filterUserData($params, $list);
            self::_filterUserData($this->_cookie, $list);
        }
        parent::__construct($params);
    }

    /**
     * @param mixed $var
     */
    protected static function _stripSlashes($var)
    {
        if (is_array($var)) {
            return array_map(array(__CLASS__, __FUNCTION__), $var);
        } else {
            return stripslashes($var);
        }
    }

    /**
     * @param mixed $var
     * @param array $globalKeys
     */
    protected static function _filterUserData(&$var, $globalKeys = array())
    {
        if (is_array($var)) {
            $var_keys = array_keys($var);
            if (array_intersect($globalKeys, $var_keys)) {
                $var = array();
            } else {
                foreach ($var_keys as $key) {
                    self::_filterUserData($var[$key], $globalKeys);
                }
            }
        } else {
            $var = str_replace("\x00", '', $var);
        }
    }

    public function hasCookie($name)
    {
        return isset($this->_cookie[$name]);
    }

    public function getCookie($name)
    {
        return $this->_cookie[$name];
    }
    
    public function isPost()
    {
        return strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') == 0;
    }

    public function getUrl()
    {
        return sprintf(
            '%s://%s%s',
            !empty($_SERVER['HTTPS']) ? 'https' : 'http',
            !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost',
            !empty($_SERVER['ORIG_REQUEST_URI']) ? $_SERVER['ORIG_REQUEST_URI'] : $_SERVER['REQUEST_URI']
        );
    }
}
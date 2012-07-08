<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Log
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

/**
 * Sabai_Log_Writer
 */
require_once 'Sabai/Log/Writer.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Log
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_Log
{
    const NONE = 0;
    const INFO = 1;
    const WARN = 4;
    const FATAL = 8;
    const ERROR_PHP_NOTICE = 32;
    const ERROR_PHP_WARNING = 64;
    const ERROR_PHP_FATAL = 128;
    const ERROR_PHP_STRICT = 256;
    const ERROR_PHP_DEPRECATED = 512;
    const ERROR_PHP = 992;
    const ERROR = 1000;
    const ALL = 1005;

    /**
     * @var int
     */
    private $_logLevel;
    /**
     * @var array
     */
    protected $_logWriters = array();

    private static $_instance;

    private function __construct(){}


    /**
     * Gets a singleton
     *
     * @return Sabai_Log
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param string $msg
     * @param string $file
     * @param string $line
     */
    public static function info($msg, $file = 'unknown', $line = 'unknown')
    {
        self::getInstance()->addLog($msg, self::INFO, $file, $line);
    }

    /**
     * @param string $msg
     * @param string $file
     * @param string $line
     */
    public static function warn($msg, $file = 'unknown', $line = 'unknown')
    {
        self::getInstance()->addLog($msg, self::WARN, $file, $line);
    }

    /**
     * @param string $msg
     * @param string $file
     * @param string $line
     */
    public static function fatal($msg, $file = 'unknown', $line = 'unknown')
    {
        self::getInstance()->addLog($msg, self::FATAL, $file, $line);
    }

    /**
     * Registers a log writer
     *
     * @param Sabai_LogWriter $logWriter
     * @param bool $append
     */
    public static function writer($logWriter, $append = true)
    {
        if ($append) {
            self::getInstance()->addLogWriter($logWriter);
        } else {
            self::getInstance()->setLogWriter($logWriter);
        }
    }

    /**
     * Gets/sets the current log level
     *
     * @return string
     * @param string $charset
     */
    public static function level($level = null)
    {
        $log = self::getInstance();
        $ret = $log->getLogLevel();
        if (!empty($level)) {
            $log->setLogLevel($level);
        }
        return $ret;
    }

    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->_logLevel;
    }

    /**
     * @param int $level
     */
    public function setLogLevel($level)
    {
        $this->_logLevel = $level;
    }

    /**
     * Adds a log writer
     *
     * @param Sabai_LogWriter $logWriter
     */
    public function addLogWriter($logWriter)
    {
        $this->_logWriters[] = $logWriter;
    }

    /**
     * Sets a log writer as the only one
     *
     * @param Sabai_LogWriter $logWriter
     */
    public function setLogWriter($logWriter)
    {
        $this->_logWriters = array($logWriter);
    }

    /**
     * @param string $msg
     * @param int $level
     * @param string $file
     * @param string $line
     */
    public function addLog($msg, $level = self::INFO, $file = 'unknown', $line = 'unknown')
    {
        if ($this->getLogLevel() & $level) {
            foreach (array_keys($this->_logWriters) as $i) {
                $this->_logWriters[$i]->writeLog($msg, $level, $file, $line);
            }
        }
    }
}
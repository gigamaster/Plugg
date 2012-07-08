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
 * @subpackage Writer
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
 * @package    Sabai_Log
 * @subpackage Writer
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_Log_Writer_HTML extends Sabai_Log_Writer
{
    /**
     * Displays a log message as HTML
     *
     * @param string $msg
     * @param int $level
     * @param string $file
     * @param int $line
     */
    public function writeLog($msg, $level, $file, $line)
    {
        switch ($level) {
        case Sabai_Log::INFO:
            $prefix = '<span style="font-weight:bold;color:#66ff00">Info</span>';
            break;
        case Sabai_Log::WARN:
            $prefix = '<span style="font-weight:bold;color:#ffcc00">Warning</span>';
            break;
        case Sabai_Log::FATAL:
            $prefix = '<span style="font-weight:bold;color:#ff0033">Error</span>';
            break;
        case Sabai_Log::ERROR_PHP_NOTICE:
            $prefix = '<span style="font-weight:bold;color:#66ff00">PHP Notice</span>';
            break;
        case Sabai_Log::ERROR_PHP_STRICT:
            $prefix = '<span style="font-weight:bold;color:#66ff00">PHP Strict</span>';
            break;
        case Sabai_Log::ERROR_PHP_DEPRECATED:
            $prefix = '<span style="font-weight:bold;color:#ffcc00">PHP Deprecated</span>';
            break;
        case Sabai_Log::ERROR_PHP_WARNING:
            $prefix = '<span style="font-weight:bold;color:#ffcc00">PHP Warning</span>';
            break;
        case Sabai_Log::ERROR_PHP_FATAL:
            $prefix = '<span style="font-weight:bold;color:#ff0033">PHP Fatal error</span>';
            break;
        default:
            $prefix = '<span style="font-weight:bold">Unknown</span>';
            break;
        }
        printf('<br />%s: %s in file <span style="font-weight:bold">%s</span> on line <span style="font-weight:bold">%s</span><br />', $prefix, h($msg), $file, $line);
    }
}
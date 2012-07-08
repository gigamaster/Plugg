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
class Sabai_Log_Writer_STDERR extends Sabai_Log_Writer
{
    /**
     * Sends a log message to STDERR
     *
     * @param string $msg
     * @param int $level
     * @param string $file
     * @param int $line
     */
    public function writeLog($msg, $level, $file, $line)
    {
        if (!STDERR) {
            return;
        }
        switch ($level) {
            case Sabai_Log::DEBUG:
                $prefix = 'Debug';
                break;
            case Sabai_Log::INFO:
                $prefix = 'Info';
                break;
            case Sabai_Log::WARN:
                $prefix = 'Warning';
                break;
            case Sabai_Log::FATAL:
                $prefix = 'Error';
                break;
            case Sabai_Log::ERROR_PHP_NOTICE:
                $prefix = 'PHP Notice';
                break;
            case Sabai_Log::ERROR_PHP_WARNING:
                $prefix = 'PHP Warning';
                break;
            case Sabai_Log::ERROR_PHP_FATAL:
                $prefix = 'PHP Fatal error';
                break;
            default:
                $prefix = 'Unknown';
                break;
        }
        fwrite(STDERR, "$prefix: $msg in file $file on line $line\n");
    }
}
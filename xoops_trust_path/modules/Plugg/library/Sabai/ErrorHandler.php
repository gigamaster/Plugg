<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_ErrorHandler
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

// For compat with PHP earlier than 5.3.0
if (!defined('E_DEPRECATED')) define('E_DEPRECATED', E_NOTICE);
if (!defined('E_USER_DEPRECATED')) define('E_USER_DEPRECATED', E_USER_NOTICE);

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_ErrorHandler
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
abstract class Sabai_ErrorHandler
{
    /**
     * Initializes a PHP error handler object
     */
    final public function init()
    {
        set_error_handler(array($this, 'handlePHPError'));
    }

    /**
     * Initializes the default PHP error handler object
     */
    public static function initDefault()
    {
        require_once 'Sabai/ErrorHandler/Default.php';
        $error_h = new Sabai_ErrorHandler_Default();
        $error_h->init();
    }

    /**
     * Handles an error triggered by PHP
     *
     * @param int $level
     * @param string $msg
     * @param string $file
     * @param int $line
     * @param array $context
     */
    abstract public function handlePHPError($level, $msg, $file, $line, $context);
}
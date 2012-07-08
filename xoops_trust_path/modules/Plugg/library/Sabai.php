<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai
 * @copyright  Copyright (c) 2008 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @link
 * @since      File available since Release 0.1.1
*/

require_once 'Sabai/Log.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai
 * @copyright  Copyright (c) 2008 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @link
 * @version    0.1.9a2
 * @since      Class available since Release 0.1.1
 */
final class Sabai
{
    private static $_started = false;

    /**
     * Initializes session and other required libraries
     *
     * @param int $logLevel
     * @param string $charset
     * @static
     */
    public static function start($logLevel = Sabai_Log::ERROR, $charset = 'UTF-8', $lang = 'en', $startSession = true)
    {
        // Some startup initializations
        define('SABAI_CHARSET', $charset);
        define('SABAI_LANG', $lang);

        // Start session if required
        if ($startSession && !session_id()) {
            @ini_set('session.use_only_cookies', 1);
            @ini_set('session.use_trans_sid', 0);
            @ini_set('session.hash_function', 1);
            @session_start();
        }

        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding(SABAI_CHARSET);
            if (function_exists('mb_regex_encoding')) {
                mb_regex_encoding(SABAI_CHARSET);
            }
            ini_set('mbstring.http_input', 'pass');
            ini_set('mbstring.http_output', 'pass');
            ini_set('mbstring.substitute_character', 'none');
        }

        // Set the global log level
        Sabai_Log::level($logLevel);

        // Initialize the default error handler
        require_once 'Sabai/ErrorHandler.php';
        Sabai_ErrorHandler::initDefault();

        self::$_started = true;
    }

    public static function started()
    {
        return self::$_started;
    }
}

/**
 * Alias for htmlspecialchars()
 *
 * @param string $str
 * @param int $quoteStyle
 * @param bool $doubleEncode
 * @return string
 */
function h($str, $quoteStyle = ENT_QUOTES, $doubleEncode = false)
{
    return htmlspecialchars($str, $quoteStyle, SABAI_CHARSET, $doubleEncode);
}

/**
 * Echos out the result of h()
 *
 * @param string $str
 * @param int $quoteStyle
 * @param bool $doubleEncode
 * @return string
 */
function _h($str, $quoteStyle = ENT_QUOTES, $doubleEncode = false)
{
    echo htmlspecialchars($str, $quoteStyle, SABAI_CHARSET, $doubleEncode);
}

/**
 * HTML friendly var_dump()
 *
 * @param mixed $var
 */
function var_dump_html($var)
{
    $args = func_get_args();
    echo '<pre>';
    _h(call_user_func_array('var_dump', $args));
    echo '</pre>';
}

/**
 * Checks whether a file can be included with include()/require()
 *
 * @param string $filename
 * @return bool
 */
function is_includable($filename)
{
    $ret = false;
    if (false !== $fp = @fopen($filename, 'r', true)) {
        $ret = true;
        fclose($fp);
    } else {
        if (!in_array('.', explode(PATH_SEPARATOR, get_include_path()))) {
            $ret = file_exists($filename);
        }
    }
    return $ret;
}

function getip($default = '')
{
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $key) {
        if (!empty($_SERVER[$key]) && ($_SERVER[$key] != 'unknown')) {
            return $_SERVER[$key];
        }
    }
    return $default;
}

/**
 * Gets truncated string with specified length
 *
 * @param string $str
 * @param int $start
 * @param int $length
 * @param string $trimmarker
 * @param string $encoding
 * @return string
 */
function mb_strimlength($str, $start, $length, $trimmarker = '...', $encoding = SABAI_CHARSET)
{
    if (strlen($str) <= $length) {
        return $str;
    }
    if (0 >= $strlen = $length - strlen($trimmarker)) {
        return mb_strcut($str, $start, $length, $encoding);
    }
    return mb_strcut($str, $start, $strlen, $encoding) . $trimmarker;
}
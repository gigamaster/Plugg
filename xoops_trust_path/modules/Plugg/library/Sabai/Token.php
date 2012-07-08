<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Token
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
 * @package    Sabai_Token
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_Token
{
    const TOKEN_PREFIX = 'Sabai_Token_';
    /**
     * @var string
     */
    private $_salt;
    /**
     * @var int
     */
    private $_expires;
    /**
     * @var string
     */
    private $_value;
    /**
     * @var bool
     */
    private static $_seeded = false;

    /**
     * Constructor
     *
     * @param string $salt
     * @param int $expires
     * @return Sabai_Token
     */
    private function __construct($salt, $expires = null)
    {
        $this->_salt = $salt;
        $this->_expires = $expires;
    }

    /**
     * Creates a new Sabai_Token object
     *
     * @return Sabai_Token
     * @param string $tokenId
     * @param int $lifetime
     */
    public static function create($tokenId, $lifetime = 1800)
    {
        // Return from session if already exists
        if ($token = self::exists($tokenId)) {
            return $token;
        }

        if (!self::$_seeded) {
            mt_srand();
            self::$_seeded = true;
        }
        $salt = function_exists('hash') ? hash('md5', uniqid(mt_rand(), true)) : md5(uniqid(mt_rand(), true));
        $token = new self($salt, time() + $lifetime);
        $session_key = self::TOKEN_PREFIX . $tokenId;
        $_SESSION[$session_key] = serialize(array($token->getSalt(), $token->getExpires()));

        return $token;
    }

    /**
     * Checks if a token with a certain ID exists
     *
     * @param string $tokenId
     * @return mixed Sabai_Token if token exists, false otherwise
     */
    public static function exists($tokenId)
    {
        $session_key = self::TOKEN_PREFIX . $tokenId;

        if (empty($_SESSION[$session_key])
            || (!$token_arr = @unserialize($_SESSION[$session_key]))
            || $token_arr[1] < time() // expired
        ) {
            unset($_SESSION[$session_key]);

            return false;
        }

        return new self($token_arr[0], $token_arr[1]);
    }

    /**
     * Destroys an existing token
     *
     * @param string $tokenId
     */
    public static function destroy($tokenId)
    {
        $session_key = self::TOKEN_PREFIX . $tokenId;
        unset($_SESSION[$session_key]);
    }

    /**
     * Validates a token
     *
     * @param string $value;
     * @param string $tokenId
     * @param bool $destoyToken
     * @return bool
     */
    public static function validate($value, $tokenId, $destoyToken = true)
    {
        if (false === $token = self::exists($tokenId)) {
            Sabai_Log::info(sprintf('Invalid token %s requested', $tokenId), __FILE__, __LINE__);
            self::destroy($tokenId);

            return false;
        }

        if ($token->getValue() != $value) {
            Sabai_Log::info('Failed validating token', __FILE__, __LINE__);
            self::destroy($tokenId);

            return false;
        }

        if ($destoyToken) self::destroy($tokenId);

        return true;
    }

    /**
     * Returns the value of this token
     *
     * @return string
     */
    public function getValue()
    {
        if (!isset($this->_value)) {
            if (function_exists('hash')) {
                $this->_value = hash('sha1', $this->_salt . $this->_expires);
            } else {
                $this->_value = sha1($this->_salt . $this->_expires);
            }
        }

        return $this->_value;
    }

    /**
     * Returns token salt value
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->_salt;
    }

    /**
     * Returns the tiemstamp at which token expires
     *
     * @return int
     */
    public function getExpires()
    {
        return $this->_expires;
    }
}
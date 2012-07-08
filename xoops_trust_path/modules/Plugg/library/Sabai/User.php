<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_User
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
 * @package    Sabai_User
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
final class Sabai_User
{
    /**
     * @var Sabai_User_Identity
     */
    protected $_identity;
    /**
     * @var bool
     */
    protected $_authenticated;
    /**
     * @var bool
     */
    protected $_superUser = false;
    /**
     * @var array
     */
    protected $_permissions = array();
    /**
     * @var bool
     */
    protected $_finalized = false;
    /**
     * @var bool
     */
    protected $_finalize = false;

    /**
     * Constructor
     *
     * @param Sabai_User_Identity $identity
     * @param bool $authenticated
     * @return Sabai_User
     */
    public function __construct(Sabai_User_AbstractIdentity $identity, $authenticated = false)
    {
        $this->_identity = $identity;
        $this->_authenticated = $authenticated;
    }

    /**
     * Magic method
     *
     * @param string $key
     */
    public function __get($key)
    {
        return $this->_identity->$key;
    }

    /**
     * Returns an identy object for the user
     *
     * @return Sabai_User_Identity
     */
    public function getIdentity()
    {
        return $this->_identity;
    }

    /**
     * Sets the user as authenticated
     *
     * @param bool $flag
     */
    public function setAuthenticated($flag = true)
    {
        $this->_authenticated = $flag;
    }

    /**
     * Checks whether this user is authenticated
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return $this->_authenticated;
    }

    /**
     * Sets the user identity as a super user
     *
     * @param bool $flag
     */
    public function setSuperUser($flag = true)
    {
        $this->_superUser = $flag;
    }

    /**
     * Checks whether this user is a super user or not
     *
     * @return bool
     */
    public function isSuperUser()
    {
        return $this->_superUser;
    }

    /**
     * Adds a permission
     *
     * @param string $perm
     */
    public function addPermission($perm)
    {
        $this->_permissions[$perm] = 1;
    }

    /**
     * Checks whether the user has a certain permission, e.g. $user->hasPermission('A').
     * Pass in an array of permission names to check if the user has one of the supplied
     * permissions, e.g. $user->hasPermission(array('A', 'B')).
     * It is also possible to check whether the user has a group of certain permissions
     * by passing in an array of permission array, e.g. $user->hasPermission(array(array('A', 'B', 'C'))).
     * For another example, in order to see whether the user has permission A or both permissions B and C
     * would be: $user->hasPermission(array('A', array('B', 'C')))
     *
     * @param mixed $perm string or array
     * @return bool
     */
    public function hasPermission($perm)
    {
        if ($this->isSuperUser()) {
            return true;
        }

        foreach ((array)$perm as $_perm) {
            foreach ((array)$_perm as $__perm) {
                if (!isset($this->_permissions[$__perm])) continue 2;
            }

            return true;
        }

        return false;
    }

    /**
     * Returns the permissions array
     *
     * @return array
     */
    public function getPermissions()
    {
        return array_keys($this->_permissions);
    }

    /**
     * Checks if the user object is finalized
     *
     * @return bool
     */
    public function isFinalized()
    {
        return $this->_finalized;
    }

    /**
     * Sets the user object as finalized
     *
     * @param bool $flag
     */
    public function setFinalized($flag = true)
    {
        $this->_finalized = $flag;
    }

    /**
     * Finalize the user object
     *
     * @param bool $flag
     */
    public function finalize($flag = null)
    {
        if (is_bool($flag)) $this->_finalize = $flag;

        return $this->_finalize;
    }
}
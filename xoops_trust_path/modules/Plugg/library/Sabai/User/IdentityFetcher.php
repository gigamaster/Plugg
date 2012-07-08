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
 * Sabai_User_AnonymousIdentity
 */
require_once 'Sabai/User/AnonymousIdentity.php';

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
 * @abstract
 */
abstract class Sabai_User_IdentityFetcher
{
    protected $_idField = 'id';
    protected $_usernameField = 'username';
    protected $_nameField = 'name';
    protected $_emailField = 'email';
    protected $_urlField = 'url';
    protected $_timestampField = 'created';
    private $_identities = array();
    private $_identitiesWithData = array();

    public function clearUserIdentities()
    {
        $this->_identities = $this->_identitiesWithData = array();
    }

    public function getUserIdentity($userId)
    {
        return $this->_identities[$userId];
    }

    /**
     * Loads user identity objects by user ids
     *
     * @param array $userIds ids of users to load
     * @param bool $withData whether or not to load extra data
     */
    public function loadUserIdentities($userIds, $withData = false)
    {
        // Check if requested identities are already oaded
        if ($withData) {
            $user_ids = array_diff($userIds, $this->_identitiesWithData);
        } else {
            $user_ids = array_diff($userIds, array_keys($this->_identities));
        }
        // Only load if there are any not loaded yet
        if ($user_ids) {
            $identities_found = $this->_doFetchUserIdentities($user_ids, $withData);
            $this->_identities = $identities_found + $this->_identities;
            if ($userids_not_found = array_diff($user_ids, array_keys($identities_found))) {
                foreach ($userids_not_found as $uid) {
                    $this->_identities[$uid] = $this->_getAnonymousUserIdentity();
                }
            }
            if ($withData) $this->_identitiesWithData += $user_ids;
        }
    }

    /**
     * Fetches a user identity object by user id
     *
     * @param string $userId
     * @param bool $withData whether or not to load extra data
     * @return Sabai_User_Identity
     */
    public function fetchUserIdentity($userId, $withData = false)
    {
        $this->loadUserIdentities(array($userId), $withData);
        return $this->getUserIdentity($userId);
    }

    /**
     * Fetches user identity objects by user ids
     *
     * @param array $userIds
     * @param bool $withData whether or not to load extra data
     * @return array array of Sabai_User_Identity objects indexed by user id
     */
    public function fetchUserIdentities($userIds, $withData = false)
    {
        $this->loadUserIdentities($userIds, $withData);
        return $this->_identities;
    }

    /**
     * Fetches user identity object by user name
     *
     * @param string $userName
     * @return Sabai_User_Identity
     */
    public function fetchUserIdentityByUsername($userName, $withData = false)
    {
        if (!$identity = $this->_doFetchUserIdentityByUsername($userName, $withData)) {
            $identity = $this->_getAnonymousUserIdentity();
        } else {
            $id = $identity->id;
            $this->_identities[$id] = $identity;
            if ($withData && !in_array($identity->id, $this->_identitiesWithData)) {
                $this->_identitiesWithData[] = $identity->id;
            }
        }
        return $identity;
    }

    /**
     * Fetches user identity object by email address
     *
     * @param string $email
     * @return Sabai_User_Identity
     */
    public function fetchUserIdentityByEmail($email, $withData = false)
    {
        if (!$identity = $this->_doFetchUserIdentityByEmail($email, $withData)) {
            $identity = $this->_getAnonymousUserIdentity();
        } else {
            $this->_identities[$identity->id] = $identity;
            if ($withData) $this->_identitiesWithData[] = $identity->id;
        }
        return $identity;
    }

    /**
     * Paginate user identity objects
     *
     * @param int $perpage
     * @param string $sort
     * @param string $order
     * @return Sabai_User_IdentityPageCollection
     */
    public function paginateIdentities($perpage = 20, $sort = 'id', $order = 'ASC', $key = 0)
    {
        require_once 'Sabai/User/IdentityPageCollection.php';
        return new Sabai_User_IdentityPageCollection($this, $perpage, $sort, $order, $key);
    }

     /**
     * Fetches user identity objects
     *
     * @return ArrayObject
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @param string $order
     */
    public function fetchIdentities($limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $order = in_array(@$order, array('ASC', 'DESC')) ? $order : 'ASC';
        switch (@$sort) {
            case 'name':
                $sort = $this->_nameField;
                break;
            case 'username':
                $sort = $this->_usernameField;
                break;
            case 'email':
                $sort = $this->_emailField;
                break;
            case 'url':
                $sort = $this->_urlField;
                break;
            case 'timestamp':
                $sort = $this->_timestampField;
                break;
            default:
                $sort = $this->_idField;
                break;
        }
        return new ArrayObject($this->_doFetchIdentities(intval($limit), intval($offset), $sort, $order));
    }

    /**
     * Fetches user identity objects
     *
     * @return array
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @param string $order
     */
    abstract protected function _doFetchIdentities($limit, $offset, $sort, $order);

    /**
     * Counts user identities
     *
     * @return int
     */
    abstract public function countIdentities();

    /**
     * Fetches user identity objects by user ids
     *
     * @abstract
     * @param array $userIds
     * @return array array of Sabai_User_Identity objects indexed by user id
     */
    abstract protected function _doFetchUserIdentities($userIds, $withData = false);

    /**
     * Fetches a user identity object by user name
     *
     * @param string $userName
     * @return mixed Sabai_User_Identity if user exists, false otherwise
     */
    abstract protected function _doFetchUserIdentityByUsername($userName, $withData = false);

    /**
     * Fetches a user identity object by email address
     *
     * @param string $email
     * @return mixed Sabai_User_Identity if user exists, false otherwise
     */
    abstract protected function _doFetchUserIdentityByEmail($email, $withData = false);

    /**
     * Creates an anonymous user identity object
     *
     * @return mixed Sabai_User_AnonymousIdentity
     */
    abstract protected function _getAnonymousUserIdentity();
}
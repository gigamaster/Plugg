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
 * @copyright  Copyright (c) 2008 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @link
 * @since      File available since Release 0.1.8
*/

/**
 * Sabai_User_IdentityFetcher
 */
require_once 'Sabai/User/IdentityFetcher.php';
/**
 * Sabai_User_IdentityFetcher
 */
require_once 'Sabai/User/AnonymousIdentity.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_User
 * @copyright  Copyright (c) 2008 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @link
 * @since      Class available since Release 0.1.8
 */
class Sabai_User_IdentityFetcher_Default extends Sabai_User_IdentityFetcher
{
    public function __construct(){}

    protected function _doFetchUserIdentities($userIds, $withData = false)
    {
        return array();
    }

    protected function _doFetchIdentities($limit, $offset, $sort, $order)
    {
        return array();
    }

    public function countIdentities()
    {
        return 0;
    }

    protected function _doFetchUserIdentityByUsername($userName, $withData = false)
    {
        return new Sabai_User_AnonymousIdentity();
    }

    protected function _doFetchUserIdentityByEmail($email, $withData = false)
    {
        return new Sabai_User_AnonymousIdentity();
    }

    protected function _getAnonymousUserIdentity()
    {
        return new Sabai_User_AnonymousIdentity();
    }
}
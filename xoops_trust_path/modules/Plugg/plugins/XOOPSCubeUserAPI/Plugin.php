<?php
class Plugg_XOOPSCubeUserAPI_Plugin extends Plugg_Plugin implements Plugg_User_Manager_API
{
    public function userLogin(Sabai_Request $request, Sabai_Application_Response $response, $returnTo)
    {
        $parsed = parse_url(XOOPS_URL);
        //$server = sprintf('%s://%s%s', $parsed['scheme'], $parsed['host'], isset($parsed['port']) ? ':' . $parsed['port'] : '');
        $server = isset($parsed['path']) ? str_replace($parsed['path'], '', XOOPS_URL) : XOOPS_URL;
        $url = XOOPS_URL . '/user.php?xoops_redirect=' . str_replace(array($server, '&', '/'), array('', urlencode('&'), urlencode('/')), $returnTo);
        $response->setHeader('Location', $url)->send();
        exit;
    }

    public function userLogout(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $response->setHeader('Location', XOOPS_URL . '/user.php?op=logout')->send();
        exit;
    }

    public function userView(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity)
    {
        $response->setHeader('Location', XOOPS_URL . '/userinfo.php?uid=' . $identity->id)->send();
        exit;
    }

    public function userRegister(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $response->setHeader('Location', XOOPS_URL . '/register.php')->send();
        exit;
    }

    public function userEdit(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity)
    {
        if ($identity->id != $this->_application->getUser()->id) {
            // Cannot edit other user's profile in XC
            $response->setError($this->_('Invalid request'));
        } else {
            $response->setHeader('Location', XOOPS_URL . '/edituser.php');
        }
        $response->send();
        exit;
    }

    public function userEditPassword(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity)
    {
        $this->userEdit($request, $identity);
    }

    public function userEditEmail(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity)
    {
        $this->userEdit($request, $identity);
    }

    public function userEditImage(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity)
    {
        if ($identity->id != $this->_application->getUser()->id) {
            // Cannot edit other user's profile in XC
            $response->setError($this->_('Invalid request'));
        } else {
            $response->setHeader('Location', XOOPS_URL . '/edituser.php?op=avatarform&uid=' . $identity->id);
        }
        $response->send();
        exit;
    }

    public function userDelete(Sabai_Request $request, Sabai_Application_Response $response, Sabai_User_Identity $identity)
    {
        if ($identity->id != $this->_application->getUser()->id) {
            // Cannot delete other user's profile in XC
            $response->setError($this->_('Invalid request'));
        } else {
            $response->setHeader('Location', XOOPS_URL . '/user.php?op=delete');
        }
        $response->send();
        exit;
    }

    public function userRequestPassword(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $response->setHeader('Location', XOOPS_URL . '/user.php?op=delete')->send();
        exit;
    }

    public function userFetchIdentitiesByIds($userIds)
    {
        $ret = array();
        $criteria = new Criteria('uid', '(' . implode(',', array_map('intval', $userIds)) . ')', 'IN');
        $xoops_users = xoops_gethandler('member')->getUsers($criteria, true);
        foreach ($userIds as $uid) {
            if (isset($xoops_users[$uid])) {
                $ret[$uid] = $this->_buildIdentity($xoops_users[$uid]);
            } else {
                $ret[$uid] = $this->userGetAnonymousIdentity();
            }
        }
        return $ret;
    }

    public function userFetchIdentitiesSortbyId($limit, $offset, $order)
    {
        return $this->_fetchIdentities($limit, $offset, 'uid', $order);
    }

    public function userFetchIdentitiesSortbyUsername($limit, $offset, $order)
    {
        return $this->_fetchIdentities($limit, $offset, 'uname', $order);
    }

    public function userFetchIdentitiesSortbyName($limit, $offset, $order)
    {
        return $this->_fetchIdentities($limit, $offset, 'name', $order);
    }

    public function userFetchIdentitiesSortbyEmail($limit, $offset, $order)
    {
        return $this->_fetchIdentities($limit, $offset, 'email', $order);
    }

    public function userFetchIdentitiesSortbyUrl($limit, $offset, $order)
    {
        return $this->_fetchIdentities($limit, $offset, 'url', $order);
    }

    public function userFetchIdentitiesSortbyTimestamp($limit, $offset, $order)
    {
        return $this->_fetchIdentities($limit, $offset, 'user_regdate', $order);
    }

    public function userFetchIdentityByUsername($userName)
    {
        $criteria = new Criteria('uname', mysql_real_escape_string($userName));
        $criteria->setLimit(1);
        $criteria->setStart(0);
        $xoops_users = xoops_gethandler('member')->getUsers($criteria, false);
        if (count($xoops_users) > 0) {
            return $this->_buildIdentity($xoops_users[0]);
        }
        return $this->userGetAnonymousIdentity();
    }

    public function userFetchIdentityByEmail($email)
    {
        if ($user = xoops_gethandler('member')->getUserByEmail($email)) {
            return $this->_buildIdentity($user);
        }
        return $this->userGetAnonymousIdentity();
    }

    private function _fetchIdentities($limit, $offset, $sort, $order)
    {
        $ret = array();
        $criteria = new CriteriaCompo();
        $criteria->setSort($sort);
        $criteria->setOrder($order);
        $criteria->setLimit($limit);
        $criteria->setStart($offset);
        $xoops_users = xoops_gethandler('member')->getUsers($criteria, false);
        foreach (array_keys($xoops_users) as $i) {
            $ret[] = $this->_buildIdentity($xoops_users[$i]);
        }
        return $ret;
    }

    public function userCountIdentities()
    {
        return xoops_gethandler('member')->getUserCount();
    }

    public function userGetIdentityPasswordById($userId)
    {
        if (!$user = xoops_gethandler('member')->getUser($userId)) return false;

        return $user->getVar('pass');
    }

    public function userGetRoleIdsById($userId)
    {
        if (!$user = xoops_gethandler('member')->getUser($userId)) return array();

        $groups = $user->getGroups();
        $module_dir = $this->_application->getId();
        $module_id = xoops_gethandler('module')->getByDirname($module_dir)->getVar('mid');

        // Return as all roles (true) if belongs to the admin group or has the module admin permission
        if (in_array(XOOPS_GROUP_ADMIN, $groups) ||
            xoops_gethandler('groupperm')->checkRight('module_admin', $module_id, $groups)
        ) {
            return true;
        }

        return xoops_gethandler('groupperm')->getItemIds($module_dir . '_role', $groups, $module_id);
    }

    public function userGetManagerSettings()
    {

    }

    public function userGetCurrentUser()
    {
        if (isset($GLOBALS['xoopsUser']) && is_object($GLOBALS['xoopsUser'])) {
            return new Sabai_User($this->_buildIdentity($GLOBALS['xoopsUser']), true);
        }

        return new Sabai_User($this->userGetAnonymousIdentity(), false);
    }

    public function userGetAnonymousIdentity()
    {
        return new Plugg_User_AnonymousIdentity($GLOBALS['xoopsConfig']['anonymous']);
    }

    private function _buildIdentity($xoopsUser)
    {
        $identity = new Plugg_User_Identity($xoopsUser->getVar('uid'), $xoopsUser->getVar('uname'));
        $identity->name = $xoopsUser->getVar('name');
        $identity->email = $xoopsUser->getVar('email');
        $identity->url = $xoopsUser->getVar('url');
        $identity->created = $xoopsUser->getVar('user_regdate');
        if ('blank.gif' !== $avatar = $xoopsUser->getVar('user_avatar')) {
            $identity->image = $identity->image_thumbnail = $identity->image_icon = XOOPS_UPLOAD_URL . '/' . $avatar;
        } else {
            $identity->image = $identity->image_thumbnail = $identity->image_icon = XOOPS_URL . '/modules/user/images/no_avatar.gif';
        }

        return $identity;
    }
}
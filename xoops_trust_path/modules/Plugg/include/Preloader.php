<?php
class Plugg_Application_XOOPSCubeLegacy_Preloader
{
    private $_app;
    private $_moduleDirname;
    private static $_initialized = false;

    public function __construct($moduleDirname)
    {
        $this->_moduleDirname = $moduleDirname;
    }

    public function preFilter($xcubeRoot)
    {
        // Allow only one module to setup delegates
        if (self::$_initialized) return;
        
        self::$_initialized = true;

        if ($xcubeRoot->mController->_mStrategy->mStatusFlag == LEGACY_CONTROLLER_STATE_ADMIN) {
            $in_admin = true;
        } else {
            $in_admin = false;
        }
        $module_dirname = $this->_moduleDirname; // common.php expects variable $module_dirname
        require dirname(__FILE__) . '/common.php';
        $this->_app = $plugg;

        $xcubeRoot->mDelegateManager->add('XoopsTpl.New', array($this, 'xoopsTplNew'));

        if ($in_admin) {
            $this->_app->setCurrentScriptName('admin');
            $xcubeRoot->mDelegateManager->add(
                'Legacy_ModuleInstallAction.InstallSuccess',
                array($this, 'adminModuleInstallSuccess'),
                XCUBE_DELEGATE_PRIORITY_FIRST
            );
            $xcubeRoot->mDelegateManager->add(
                'Legacy_ModuleUninstallAction.UninstallSuccess',
                array($this, 'adminModuleUninstallSuccess'),
                XCUBE_DELEGATE_PRIORITY_FIRST
            );
            $xcubeRoot->mDelegateManager->add(
                'Legacy_ModuleUpdateAction.UpdateSuccess',
                array($this, 'adminModuleUpdateSuccess'),
                XCUBE_DELEGATE_PRIORITY_FIRST
            );
        } else {
            if (($xoopscube_plugin = $this->_app->getPlugin('XOOPSCube'))) {
                if (!$xoopscube_plugin->getConfig('useXoopsSearch')) {
                    $xcubeRoot->mDelegateManager->add(
                        'Legacypage.Search.Access',
                        array($this, 'searchAccess'),
                        XCUBE_DELEGATE_PRIORITY_FIRST
                    );
                }
            }
        }

        // Check plugin availability
        if ((!$xoopscube_plugin = $this->_app->getPlugin('XOOPSCube'))
            || (!$user_plugin = $this->_app->getPlugin('User')) // User plugin must be installed and active
            || (!$userman_plugin_name = $user_plugin->getConfig('userManagerPlugin')) // User manager plugin must be defined
            || $userman_plugin_name == 'XOOPSCubeUserAPI' // User manager plugin should be other than XOOPSCubeUserAPI
            || (!$userman_plugin = $this->_app->getPlugin($userman_plugin_name)) // User manager plugin must be installed and active
        ) {
            return;
        }

        // Make sure all users are allowed to access this module
        $module_id = xoops_gethandler('module')->getByDirname($module_dirname)->getVar('mid');
        $gperm_handler = xoops_gethandler('groupperm');
        if (!$gperm_handler->checkRight('module_read', $module_id, XOOPS_GROUP_ANONYMOUS)
            || !$gperm_handler->checkRight('module_read', $module_id, XOOPS_GROUP_USERS)
        ) {
            trigger_error(sprintf(
                'Module %s needs to be accessible by all user groups to enable the user management plugin %s.',
                $module_dirname, $userman_plugin_name),
                E_USER_WARNING
            );
            return;
        }

        // Set delegates
        $xcubeRoot->mController->mSetupUser->reset();
        $xcubeRoot->mController->mSetupUser->add(array($this, 'setupUser'), XCUBE_DELEGATE_PRIORITY_FIRST);
        if (!$in_admin) {
            $xcubeRoot->mDelegateManager->add(
                'Legacypage.Edituser.Access',
                array($this, 'edituserAccess'),
                XCUBE_DELEGATE_PRIORITY_FIRST
            );
            $xcubeRoot->mDelegateManager->add(
                'Legacypage.Lostpass.Access',
                array($this, 'lostpassAccess'),
                XCUBE_DELEGATE_PRIORITY_FIRST
            );
            $xcubeRoot->mDelegateManager->add(
                'Legacypage.Register.Access',
                array($this, 'registerAccess'),
                XCUBE_DELEGATE_PRIORITY_FIRST
            );
            $xcubeRoot->mDelegateManager->add(
                'Legacypage.User.Access',
                array($this, 'userAccess'),
                XCUBE_DELEGATE_PRIORITY_FIRST
            );
            $xcubeRoot->mDelegateManager->add(
                'Legacypage.Userinfo.Access',
                array($this, 'userinfoAccess'),
                XCUBE_DELEGATE_PRIORITY_FIRST
            );

            // Replace the PM module with the Plugg message plugin if the plugin installed and active
            if ($this->_app->getPlugin('Message')) {
                $xcubeRoot->mDelegateManager->add(
                    'Legacypage.Viewpmsg.Access',
                    array($this, 'viewpmsgAccess'),
                    XCUBE_DELEGATE_PRIORITY_FIRST
                );
                $xcubeRoot->mDelegateManager->add(
                    'Legacypage.Pmlite.Access',
                    array($this, 'viewpmsgAccess'),
                    XCUBE_DELEGATE_PRIORITY_FIRST
                );
                $xcubeRoot->mDelegateManager->add(
                    'Legacypage.Readpmsg.Access',
                    array($this, 'viewpmsgAccess'),
                    XCUBE_DELEGATE_PRIORITY_FIRST
                );
            }

            $xcubeRoot->mDelegateManager->add(
                'Legacy.Event.UserDelete',
                array($this, 'userDeleteSuccess'),
                XCUBE_DELEGATE_PRIORITY_FIRST
            );
        } else {
            $xcubeRoot->mDelegateManager->add(
                'Legacy.Admin.Event.UserDelete.Success',
                array($this, 'userDeleteSuccess'),
                XCUBE_DELEGATE_PRIORITY_FIRST
            );
        }
    }

    public function postFilter($xcubeRoot)
    {
        // Clear some session variables when pages outside the module
        if (!isset($GLOBALS['xoopsModule'])) {
            $this->_app->unsetSessionVar('request_url');
            $this->_app->unsetSessionVar('flash');
        }
    }

    public function xoopsTplNew($xoopsTpl)
    {
        // Assign CSS to xoops_module_header when not running the Plugg module
        if (isset($GLOBALS['xoopsModule'])
            && $GLOBALS['xoopsModule']->getVar('dirname') == $this->_moduleDirname
        ) {
            return;
        }

        if (!$plugins_last_update = $this->_app->getPluginManager()->getPluginsLastLoaded()) {
            $plugins_last_update = time();
        }
        $xoops_module_header = array(
            $xoopsTpl->get_template_vars('xoops_module_header'),
            sprintf(
                '<link rel="stylesheet" type="text/css" media="screen" href="%s/modules/%s/layouts/default/css/widget.css" />
                 <link rel="stylesheet" type="text/css" media="screen" href="%s" />',
                XOOPS_URL,
                $this->_moduleDirname,
                $this->_createUrl('/system/minify/css/' . $plugins_last_update)
            )
        );
        $xoopsTpl->assign('xoops_module_header', implode(PHP_EOL, $xoops_module_header));
    }

    public function setupUser($principal, $controller, $context)
    {
        if ($this->_app->getUser()->isAuthenticated()) {
            if ($context->mXoopsUser = xoops_gethandler('member')->getUser($this->_app->getUser()->id)) {
                if (!isset($_SESSION['xoopsUserGroups'])) {
                    $_SESSION['xoopsUserGroups'] = $context->mXoopsUser->getGroups();
                } else {
                    $context->mXoopsUser->setGroups($_SESSION['xoopsUserGroups']);
                }
                $roles = array('Site.RegisteredUser');
                if ($context->mXoopsUser->isAdmin(-1)) {
                    $roles[] = 'Site.Administrator';
                }
                if (in_array(XOOPS_GROUP_ADMIN, $_SESSION['xoopsUserGroups'])) {
                    $roles[] = 'Site.Owner';
                }
                $principal = new Legacy_GenericPrincipal(new Legacy_Identity($context->mXoopsUser), $roles);

                return;
            }
        }
        unset($_SESSION['xoopsUserGroups']);
        $context->mXoopsUser = null;
        $principal = new Legacy_GenericPrincipal(new Legacy_AnonymousIdentity(), array('Site.GuestUser'));
    }

    public function edituserAccess()
    {
       switch (@$_GET['op']) {
           case 'avatarform':
           case 'avatarupload':
           case 'avatarchoose':
                header('Location: ' . $this->_createUrl('/user/edit/image'));
                exit;
           default:
                header('Location: ' . $this->_createUrl('/user/edit'));
                exit;
        }
    }

    public function lostpassAccess()
    {
        header('Location: ' . $this->_createUrl('/user/request_password'));
        exit;
    }

    public function registerAccess()
    {
        header('Location: ' . $this->_createUrl('/user/register'));
        exit;
    }

    public function userAccess()
    {
        $params = array();
        if (!$op = @$_GET['op']) {
            if (!is_object(@$GLOBALS['xoopsUser'])) {
                $op = 'login';
            }
        }
        switch ($op) {
            case 'login':
                $route = '/user/login';
                if ($xoops_redirect = trim((string)@$_GET['xoops_redirect'])) {
                    $parsed = parse_url(XOOPS_URL);
                    $redirect = $parsed['scheme'] . '://' . $parsed['host'];
                    if (isset($parsed['port'])) $redirect .= ':' . $parsed['port'];
                    $redirect .= $xoops_redirect;
                    $params = array('return' => 1, 'return_to' => $redirect);
                }
                break;
            case 'logout':
                unset($_SESSION['xoopsUserGroups']);
                $route = '/user/logout';
                break;
            case 'delete':
                $route = '/user/settings/delete';
                break;
            default:
                $route = '/user';
        }
        header('Location: ' . $this->_createUrl($route, $params));
        exit;
    }

    public function userinfoAccess()
    {
        $route = ($user_id = (int)@$_GET['uid']) ? '/user/' . $user_id : '/user';
        header('Location: ' . $this->_createUrl($route));
        exit;
    }

    public function searchAccess()
    {
        header('Location: ' . $this->_createUrl('/search'));
        exit;
    }

    public function viewpmsgAccess()
    {
        header('Location: ' . $this->_createUrl('/user/messages'));
        exit;
    }

    public function userDeleteSuccess($xoopsUser)
    {
        $uid = $xoopsUser->getVar('uid');
        $identity = new Plugg_User_Identity($uid, $xoopsUser->getVar('uname'));
        $identity->name = $xoopsUser->getVar('name');
        $identity->email = $xoopsUser->getVar('email');
        $identity->url = $xoopsUser->getVar('url');
        $identity->created = $xoopsUser->getVar('user_regdate');
        $identity->image = $identity->image_thumbnail = $identity->image_icon = XOOPS_URL . '/uploads/' . $xoopsUser->getVar('user_avatar');
        $this->_app->DispatchEvent('UserIdentityDeleteSuccess', array($identity));
    }

    public function adminModuleInstallSuccess($module, &$log)
    {
        $this->_app->DispatchEvent('XOOPSCubeModuleInstallSuccess', array($module));
    }

    public function adminModuleUninstallSuccess($module, &$log)
    {
        $this->_app->DispatchEvent('XOOPSCubeModuleUninstallSuccess', array($module));
    }

    public function adminModuleUpdateSuccess($module, &$log)
    {
        $this->_app->DispatchEvent('XOOPSCubeModuleUpdateSuccess', array($module));
    }

    private function _createUrl($route, $params = array(), $separator = '&')
    {
        return $this->_app->createUrl(array('base' => $route, 'params' => $params, 'separator' => $separator));
    }
}
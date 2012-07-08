<?php
class Plugg_User_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_System_Routable_Admin, Plugg_User_Permissionable, Plugg_Widgets_Widget, Plugg_AdminWidget_Widget, Plugg_Form_Field
{
    const WIDGET_TYPE_PUBLIC = 1, WIDGET_TYPE_PRIVATE = 2, WIDGET_TYPE_CACHEABLE = 4,
        WIDGET_POSITION_TOP = 0, WIDGET_POSITION_LEFT = 1, WIDGET_POSITION_RIGHT = 2, WIDGET_POSITION_BOTTOM = 3,
        AUTOLOGIN_COOKIE = 'plugg_user_autologin',
        QUEUE_TYPE_REGISTER = 0, QUEUE_TYPE_REGISTERAUTH = 1, QUEUE_TYPE_EDITEMAIL = 2,
        QUEUE_TYPE_REQUESTPASSWORD = 3;

    private $_identityFetcher;

    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/user' => array(
                'controller' => 'Index',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/user/:user_id' => array(
                'access_callback' => true,
                'title_callback' => true,
                'format' => array(':user_id' => '\d+'),
                'controller' => 'Identity',
            ),
            '/user/:user_id/widget/:plugin_name/:widget_name' => array(
                'access_callback' => true,
                'format' => array(':plugin_name' => '\w+', ':widget_name' => '\w+'),
                'controller' => 'Identity_Widget',
            ),
            '/user/:user_id/edit' => array(
                'access_callback' => true,
                'title_callback' => true,
                'controller' => 'Identity_Edit',
                'title' => $this->_('Edit profile'),
                'type' => Plugg::ROUTE_MENU,
                'weight' => 10
            ),
            '/user/:user_id/edit/image' => array(
                'controller' => 'Identity_Edit_Image',
                'title' => $this->_('Edit image'),
                'access_callback' => true
            ),
            '/user/:user_id/edit/status' => array(
                'controller' => 'Identity_Edit_Status',
                'title' => $this->_('Update status'),
                'access_callback' => true
            ),
            '/user/:user_id/settings' => array(
                'access_callback' => true,
                'title_callback' => true,
                'controller' => 'Identity_Settings',
                'title' => $this->_('Settings'),
                'type' => Plugg::ROUTE_TAB,
                'weight' => 99
            ),
            '/user/:user_id/settings/autologins' => array(
                'controller' => 'Identity_Settings_Autologins',
                'title' => $this->_('Autologins'),
                'type' => Plugg::ROUTE_TAB,
                'weight' => 5,
            ),
            '/user/:user_id/settings/widgets' => array(
                'controller' => 'Identity_Settings_Widgets',
                'title' => $this->_('Widgets'),
                'type' => Plugg::ROUTE_TAB,
                'access_callback' => true,
            ),
            '/user/:user_id/settings/widgets/submit' => array(
                'controller' => 'Identity_Settings_Widgets_Submit',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/user/:user_id/settings/widgets/:widget_id/edit' => array(
                'controller' => 'Identity_Settings_Widgets_EditWidget',
                'format' => array(':widget_id' => '\d+'),
                'access_callback' => true
            ),
            '/user/:user_id/settings/email' => array(
                'controller' => 'Identity_Settings_Email',
                'title' => $this->_('Edit email'),
                'type' => Plugg::ROUTE_MENU,
                'access_callback' => true
            ),
            '/user/:user_id/settings/password' => array(
                'controller' => 'Identity_Settings_Password',
                'title' => $this->_('Edit password'),
                'type' => Plugg::ROUTE_MENU,
                'access_callback' => true
            ),
            '/user/:user_id/settings/delete' => array(
                'access_callback' => true,
                'controller' => 'Identity_Settings_Delete',
                'title' => $this->_('Delete account'),
                'type' => Plugg::ROUTE_MENU,
                'weight' => 20
            ),
            '/user/:user_id/status' => array(
                'controller' => 'Identity_Status',
                'title' => $this->_('Status message'),
            ),
            '/user/:user_id/logout' => array(
                'controller' => 'Identity_Logout',
                'title' => $this->_('Logout'),
                'type' => Plugg::ROUTE_MENU,
                'weight' => 20,
                'access_callback' => true,
            ),
            '/user/profile/:user_name' => array(
                'controller' => 'ViewProfile',
                'type' => Plugg::ROUTE_CALLBACK,
                'format' => array(':user_name' => '[a-zA-Z0-9_\-]+')
            ),
            '/user/login' => array(
                'controller' => 'Login',
                'title' => $this->_('Login'),
            ),
            '/user/login/:auth_plugin' => array(
                'controller' => 'LoginAuth',
                'title' => $this->_('Login'),
                'access_callback' => true,
            ),
            '/user/logout' => array(
                'controller' => 'Logout',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/user/register' => array(
                'controller' => 'Register',
                'title' => $this->_('Create an account'),
            ),
            '/user/edit' => array(
                'controller' => 'Edit',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/user/settings' => array(
                'controller' => 'Settings',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/user/request_password' => array(
                'controller' => 'RequestPassword',
                'title' => $this->_('Request password'),
            ),
            '/user/confirm/:queue_id' => array(
                'controller' => 'Confirm',
                'format' => array(':queue_id' => '\d+'),
            ),
            '/user/associate_auth' => array(
                'controller' => 'AssociateAuth',
                'title' => $this->_('Login'),
            ),
            '/user/register_auth' => array(
                'controller' => 'RegisterAuth',
                'title' => $this->_('Create account'),
            ),
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/user/:user_id':
                if ((!$identity = $this->_getRequestedUserIdentity($request))
                    || (!$manager = $this->getManagerPlugin())
                ) return false;
                
                if (($manager = $this->getManagerPlugin()) instanceof Plugg_User_Manager_API) {
                    $manager->userView($request, $response, $identity);

                    return false;
                }

                $this->_application->setData(array(
                    'identity' => $identity,
                    'identity_is_me' => $this->_application->getUser()->id == $identity->id,
                ));
                
                $response->setPageTitle($identity->display_name, true) // set the top page title
                    ->setHtmlHeadTitle($identity->display_name);

                return true;

            case '/user/:user_id/widget/:plugin_name/:widget_name':
                if ((!$plugin_name = $request->asStr('plugin_name'))
                    || (!$widget_name = $request->asStr('widget_name'))
                    || (!$plugin = $this->_application->getPlugin($plugin_name))
                ) return false;

                $widget = $this->getModel()->Widget->criteria()->plugin_is($plugin_name)
                    ->name_is($widget_name)->fetch()->getFirst();

                if (!$widget) return false;

                $this->_application->widget = $widget;
                $this->_application->widget_plugin = $plugin;

                return true;

            case '/user/:user_id/edit':
                if (($manager = $this->getManagerPlugin()) instanceof Plugg_User_Manager_API) {
                    $manager->userEdit($request, $response, $this->_application->identity);

                    return false;
                }

                if (!$this->_application->getUser()->isAuthenticated()) return false;

                if (!$this->_application->identity_is_me) return $this->_application->getUser()->hasPermission('user profile edit any');

                return $this->_application->getUser()->hasPermission('user profile edit own');

           case '/user/:user_id/logout':
               if (!$this->_application->identity_is_me ||
                   !$this->_application->getUser()->isAuthenticated()
               ) return false;
               
                if (($manager = $this->getManagerPlugin()) instanceof Plugg_User_Manager_API) {
                    $manager->userLogout($request, $response, $this->_application->identity);

                    return false;
                }

                return true;
                
           case '/user/:user_id/edit/image':
                if (($manager = $this->getManagerPlugin()) instanceof Plugg_User_Manager_API) {
                    $manager->userEditImage($request, $response, $this->_application->identity);

                    return false;
                }

                if (!$manager instanceof Plugg_User_Manager_ApplicationWithImage) return false;

                if (!$this->_application->identity_is_me) return $this->_application->getUser()->hasPermission('user image edit any');

                return $this->_application->getUser()->hasPermission('user image edit own');

            case '/user/:user_id/edit/status':
                return $this->_application->identity_is_me || $this->_application->getUser()->hasPermission('user status edit any');
                
            case '/user/:user_id/settings':
                if (($manager = $this->getManagerPlugin()) instanceof Plugg_User_Manager_API) {
                    $manager->userEdit($request, $response, $this->_application->identity);

                    return false;
                }

                if (!$this->_application->getUser()->isAuthenticated()) return false;

                return $this->_application->identity_is_me || $this->_application->getUser()->hasPermission('user account manage any');
                
            case '/user/:user_id/settings/widgets':
                if (!$this->_application->identity_is_me) return $this->_application->getUser()->hasPermission('user widget edit any');

                return $this->_application->getUser()->hasPermission('user widget edit own');

            case '/user/:user_id/settings/widgets/:widget_id/edit':
                return ($this->_application->widget = $this->getRequestedEntity($request, 'Widget', 'widget_id')) ? true : false;

            case '/user/:user_id/settings/email':
                if (($manager = $this->getManagerPlugin()) instanceof Plugg_User_Manager_API) {
                    $manager->userEditEmail($request, $response, $this->_application->identity);

                    return false;
                }

                if (!$this->_application->identity_is_me) return $this->_application->getUser()->hasPermission('user account edit email any');

                return $this->_application->getUser()->hasPermission('user account email edit own');

            case '/user/:user_id/settings/password':
                if (($manager = $this->getManagerPlugin()) instanceof Plugg_User_Manager_API) {
                    $manager->userEditPassword($request, $response, $this->_application->identity);

                    return false;
                }

                if (!$this->_application->identity_is_me) return $this->_application->getUser()->hasPermission('user account password edit any');

                return $this->_application->getUser()->hasPermission('user account password edit own');

            case '/user/:user_id/settings/delete':
                if (($manager = $this->getManagerPlugin()) instanceof Plugg_User_Manager_API) {
                    $manager->userDelete($request, $response, $this->_application->identity);

                    return false;
                }

                if (!$this->_application->getUser()->isAuthenticated()) return false;

                if (!$this->_application->identity_is_me) return $this->_application->getUser()->hasPermission('user account delete any');

                return !$this->_application->getUser()->isSuperUser()
                    && $this->_application->getUser()->hasPermission('user account delete own');

            case '/user/login/:auth_plugin':
                return (($plugin_name = $request->asStr('auth_plugin'))
                    && ($this->_application->auth_plugin = $this->_application->getPlugin($plugin_name))
                    && ($this->_application->auth = $this->getModel()->Auth->criteria()->active_is(1)
                         ->plugin_is($plugin_name)->fetch()->getFirst())
                 );
        }
    }

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/user/:user_id':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT
                    ? $this->_('Profile')
                    : $this->_application->identity->display_name;

            case '/user/:user_id/edit':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Profile') : $title;
                
            case '/user/:user_id/settings':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Account') : $title;
        }
    }

    /* Start implementation of Plugg_System_Routable_Main */

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/user' => array(
                'controller' => 'Index',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('User management'),
                'title_callback' => true,
            ),
            '/user/submit' => array(
                'controller' => 'Submit',
                'type' => Plugg::ROUTE_CALLBACK
            ),
            '/user/auths' => array(
                'controller' => 'Auths',
                'title' => $this->_('Authentications'),
                'type' => Plugg::ROUTE_TAB,
                'weight' => 3,
                'title_callback' => true,
            ),
            '/user/auths/install' => array(
                'controller' => 'Auths_Install',
                'title' => $this->_('Install new'),
                'type' => Plugg::ROUTE_MENU,
            ),
            '/user/auths/:auth_id' => array(
                'controller' => 'Auths_Auth',
                'format' => array(':auth_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/user/auths/:auth_id/configure' => array(
                'controller' => 'Auths_Auth_Configure',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Configure'),
                'access_callback' => true,
            ),
            '/user/auths/:auth_id/authdatas' => array(
                'controller' => 'Auths_Auth_Authdatas',
                'title' => $this->_('Listing accounts'),
            ),
            '/user/auths/:auth_id/authdatas/submit' => array(
                'controller' => 'Auths_Auth_Authdatas_Submit',
                'type' => Plugg::ROUTE_CALLBACK
            ),
            '/user/auths/autologins' => array(
                'controller' => 'Auths_Autologins',
                'title' => $this->_('Autologins'),
                'type' => Plugg::ROUTE_TAB,
                'weight' => 20,
            ),
            '/user/auths/autologins/submit' => array(
                'controller' => 'Auths_Autologins_Submit',
                'type' => Plugg::ROUTE_CALLBACK
            ),
            '/user/fields' => array(
                'controller' => 'Fields',
                'title' => $this->_('Fields'),
                'type' => Plugg::ROUTE_TAB,
                'weight' => 2,
            ),
            '/user/fields/edit/field' => array(
                'controller' => 'Fields_EditField',
                'title' => $this->_('Edit field'),
                'access_callback' => true,
            ),
            '/user/fields/edit/fieldset' => array(
                'controller' => 'Fields_EditFieldset',
                'title' => $this->_('Edit fieldset'),
            ),
            '/user/fields/submit' => array(
                'controller' => 'Fields_Submit',
                'type' => Plugg::ROUTE_CALLBACK
            ),
            '/user/queues' => array(
                'controller' => 'Queues',
                'title' => $this->_('Queues'),
                'type' => Plugg::ROUTE_TAB,
                'weight' => 10,
            ),
            '/user/queues/:queue_id' => array(
                'format' => array(':queue_id' => '\d+'),
                'access_callback' => true,
            ),
            '/user/queues/:queue_id/send' => array(
                'controller' => 'Queues_SendQueue',
            ),
            '/user/queues/submit' => array(
                'controller' => 'Queue_Submit',
                'type' => Plugg::ROUTE_CALLBACK
            ),
            '/user/roles' => array(
                'controller' => 'Roles',
                'title' => $this->_('Roles'),
                'type' => Plugg::ROUTE_TAB,
                'weight' => 1,
            ),
            '/user/roles/add' => array(
                'controller' => 'Roles_AddRole',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Add role'),
            ),
            '/user/roles/:role_id' => array(
                'controller' => 'Roles_Role',
                'format' => array(':role_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/user/roles/:role_id/edit' => array(
                'controller' => 'Roles_Role_Update',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Edit role'),
            ),
            '/user/roles/:role_id/add_member' => array(
                'forward' => '/user/roles/:role_id/members/add',
                'type' => Plugg::ROUTE_MENU,
                'title' => $this->_('Add member'),
            ),
            '/user/roles/:role_id/members' => array(
                'controller' => 'Roles_Role_Members',
            ),
            '/user/roles/:role_id/members/add' => array(
                'controller' => 'Roles_Role_AddMember',
                'title' => $this->_('Add member'),
                'type' => Plugg::ROUTE_MENU,
            ),
            '/user/widgets' => array(
                'controller' => 'Widgets',
                'title' => $this->_('Widgets'),
                'type' => Plugg::ROUTE_TAB,
                'weight' => 4,
            ),
            '/user/widgets/submit' => array(
                'controller' => 'Widgets_Submit',
                'type' => Plugg::ROUTE_CALLBACK
            ),
            '/user/widgets/:widget_id' => array(
                'format' => array(':widget_id' => '\d+'),
                'access_callback' => true,
            ),
            '/user/widgets/:widget_id/edit' => array(
                'controller' => 'Widgets_EditWidget',
            ),
            '/user/settings' => array(
                'controller' => 'Settings',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Settings'),
                'title_callback' => true,
                'weight' => 30,
            ),
            '/user/settings/email' => array(
                'controller' => 'Settings_Email',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Email settings')
            ),
            '/user/settings/manager/:plugin_name' => array(
                'controller' => 'Settings_Manager',
                'format' => array(':plugin_name' => '\w+'),
                'title_callback' => true,
                'access_callback' => true,
            )
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/user/auths/:auth_id':
                // Make sure the requested auth plugin exists and active
                return ($this->_application->auth = $this->getRequestedEntity($request, 'Auth'))
                    && $this->_application->getPlugin($this->_application->auth->plugin);

            case '/user/auths/:auth_id/configure':
                // Check whether the auth plugin has any configurable options
                return $this->_application->getPlugin($this->_application->auth->plugin)
                    ->userAuthGetSettings($this->_application->auth->name);
                    
            case '/user/fields/edit/field':  
                return (($field_id = $request->asInt('field_id')) 
                    && ($this->_application->field = $this->_application->getPlugin('Form')->getModel()->Field->fetchById($field_id)));

            case '/user/queues/:queue_id':
                // Make sure the requested queue data exists and passed a valid key
                return ($this->_application->queue = $this->getRequestedEntity($request, 'Queue'))
                    && $this->_application->queue->key == $request->asStr('key');

            case '/user/roles/:role_id':
                // Make sure the requested role exists
                return ($this->_application->role = $this->getRequestedEntity($request, 'Role')) ? true : false;

            case '/user/widgets/:widget_id':
                // Make sure the requested member data exists
                return ($this->_application->widget = $this->getRequestedEntity($request, 'Widget')) ? true : false;

            case '/user/settings/manager/:plugin_name':
                // Make sure a valid user manager plugin is requested
                return ($this->_application->manager_plugin = $this->_application->getPlugin($request->asStr('plugin_name')))
                    && $this->_application->manager_plugin instanceof Plugg_User_Manager;
        }
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/user':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Users') : $title;
            case '/user/auths':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('List') : $title;
            case '/user/auths/:auth_id':
                return $this->_application->auth->title;
            case '/user/roles/:role_id':
                return $this->_application->role->display_name;
            case '/user/settings':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('General') : $title;
            case '/user/settings/manager/:plugin_name':
                return $this->_application->manager_plugin->nicename;
        }
    }

    /* End implementation of Plugg_System_Routable_Admin */

    /* Start implementation of Plugg_Form_Field */

    public function formFieldGetFormElementTypes()
    {
        return array('user_select_menu' => Plugg_Form_Plugin::FORM_FIELD_SYSTEM);
    }
    
    public function formFieldGetTitle($type)
    {
        // System fields do not need to return any value
    }
    
    public function formFieldGetSummary($type)
    {
        // System fields do not need to return any value
    }
    
    public function formFieldGetFormElement($type, $name, array &$data, Plugg_Form_Form $form)
    {
        switch ($type) {
            case 'user_select_menu':
                $options = array();
                $plugins = $this->_application->getPluginManager()->getInstalledPluginsByInterface('Plugg_User_Menu');
                foreach (array_keys($plugins) as $plugin_name) {
                    if (!$plugin = $this->_application->getPlugin($plugin_name)) continue;
                    foreach ($plugin->userMenuGetNames() as $menu_name) {
                        $options[$plugin->name . '_' . $menu_name] = $plugin->userMenuGetNicename($menu_name);
                    }
                }
                $data['#options'] = $options;
                
                return $form->createElement('checkboxes', $name, $data);
        }
    }

    public function formFieldOnSubmitForm($type, $name, &$value, array &$data, Plugg_Form_Form $form){}
    
    public function formFieldOnCleanupForm($type, $name, array $data, Plugg_Form_Form $form){}

    public function formFieldGetSettings($type, array $currentValues){}
    
    public function formFieldRenderHtml($type, $value, array $data, array $allValues = array())
    {
        switch ($type) {
            case 'user_select_menu':
                return $this->_application->getPlugin('Form')->formFieldRenderHtml('checkboxes', $value, $data, $allValues);
        }
    }

    /* End implementation of Plugg_Form_Field */

    private function _getRequestedUserIdentity(Sabai_Request $request)
    {
        if ($id = $request->asInt('user_id')) {
            $identity = $this->getIdentity($id, true);

            if (!$identity->isAnonymous()) return $identity;
        }

        return false;
    }

    public function getLoginForm($action = null, $autologin = null)
    {
        $form = $this->getManagerPlugin()->userLoginGetForm($this->getDefaultLoginForm());
        if (empty($action)) $action = $this->_application->getUrl('/user/login');
        $form['#action'] = $action;

        // Add autologin settings
        if ($this->getConfig('enableAutologin')) {
            if (!is_null($autologin)) {
                $form['_autologin'] = array(
                    '#type' => 'hidden',
                    '#value' => intval($autologin),
                );
            } else {
                $days = $this->getConfig('autologinSessionLifetime');
                $form['_autologin'] = array(
                    '#type' => 'checkbox',
                    '#title' => sprintf($this->_n('Remember me on this computer for 1 day.', 'Remember me on this computer for %d days.', $days), $days),
                );
            }
        }

        return $form;
    }

    public function submitLoginForm(Plugg_Form_Form $form)
    {
        if ($result = $this->getManagerPlugin()->userLoginSubmitForm($form)) {
            if (is_object($result)
                && $result instanceof Sabai_User_Identity
                && !$result->isAnonymous()
            ) {
                return new Sabai_User($result, true);
            }
        }

        return false;
    }

    public function getDefaultLoginForm()
    {
        $settings = array(
            '#method' => 'post',
            '#action' => '',
            '#header' => array(sprintf(
                $this->_('Please enter your details below to login. Or <a href="%s">create a new account</a> if you are still not registered.'),
                $this->getUrl('register')
            )),
            'username' => array(
                '#title' => $this->_('Username'),
                '#size' => 30,
                '#maxlength' => 255,
                '#required' => true,
            ),
            'password' => array(
                '#type' => 'password',
                '#title' => $this->_('Password'),
                '#description' => sprintf(
                    $this->_('<a href="%s">Forgot your password?</a>'),
                    $this->getUrl('request_password')
                ),
                '#size' => 30,
                '#required' => true,
            ),
        );

        return $settings;
    }

    public function loginUser(Sabai_Request $request, Sabai_Application_Response $response, $user, $autologin = false)
    {
        if (!$this->getManagerPlugin()->userLoginUser($user)) return false;

        $url = array();
        if (!empty($_SESSION['Plugg_User_Main_Login_return'])) {
            $url = $_SESSION['Plugg_User_Main_Login_return'];
            unset($_SESSION['Plugg_User_Main_Login_return']);
            // Make sure the URL is a local URL
            if (!$this->_application->isLocalUrl($url)) $url = array();
        }

        $this->_application->setUser($user);
        $response->setSuccess($this->_('You have logged in successfully.'), $url);
        if (!empty($autologin)) $this->_createAutologinSession($user);
        $this->_application->DispatchEvent('UserLoginSuccess', array($this->_application->getUser()));

        return true;
    }

    public function getRegisterForm($username = null, $email = null, $name = null)
    {
        $settings = $this->getManagerPlugin()->userRegisterGetForm($username, $email, $name);

        // Add extra form field settings
        $this->addExtraFormFields($settings);

        return $settings;
    }

    public function hasCurrentUser()
    {
        $manager = $this->getManagerPlugin();
        if ($user = $manager->userGetCurrentUser()) return $user;

        // Auto login user if the autoloign feature is enabled and valid cookie is set
        if ($this->getConfig('enableAutologin') && $manager instanceof Plugg_User_Manager_Application) {
            if (isset($_COOKIE[self::AUTOLOGIN_COOKIE])) {
                if (($cookie = explode(':', $_COOKIE[self::AUTOLOGIN_COOKIE]))
                    && ($user_id = $cookie[0])
                    && ($pass = $cookie[1])
                    && ($session = $this->_getAutologinSession($user_id))
                    && $session->expires > time()
                    && ($identity = $session->User)
                    && sha1($session->salt . $manager->userGetIdentityPasswordById($identity->id)) == $pass
                ) {
                    $user = new Sabai_User($identity, true);
                    if ($manager->userLoginUser($user)) {
                        $session->last_ip = getip();
                        $session->last_ua = $_SERVER['HTTP_USER_AGENT'];
                        $session->commit();

                        return $user;
                    }
                }
                // Invalidate cookie
                $this->_setAutologinCookie('', time() - 3600);
            }
        }

        return false;
    }

    public function getCurrentUser()
    {
        if ($user = $this->hasCurrentUser()) return $user;

        return new Sabai_User($this->getManagerPlugin()->userGetAnonymousIdentity(), false);
    }

    private function _getAutologinSession($userId)
    {
        return $this->getModel()->Autologin->criteria()->userId_is($userId)->fetch(1, 0)->getFirst();
    }

    private function _createAutologinSession($user)
    {
        $user_id = $user->id;
        if (!$session = $this->_getAutologinSession($user_id)) {
            $session = $this->getModel()->create('Autologin');
            $session->salt = md5(uniqid(mt_rand(), true));
            $session->assignUser($user);
            $session->markNew();
        } else {
            if ($this->getConfig('limitSingleAutologinSession')) {
                // Update salt so that only the requested PC holds a valid autologin cookie
                $session->salt = md5(uniqid(mt_rand(), true));
            }
        }
        $expires = time() + 86400 * intval($this->getConfig('autologinSessionLifetime'));
        $session->expires = $expires;
        $session->last_ip = getip();
        $session->last_ua = $_SERVER['HTTP_USER_AGENT'];
        if (!$session->commit()) {
            return false;
        }
        $password = sha1($session->salt . $this->getManagerPlugin()->userGetIdentityPasswordById($user->id));
        $this->_setAutologinCookie("$user_id:$password", $expires);
        return true;
    }

    private function _setAutologinCookie($value, $expires)
    {
        $path = '/';
        if ($parsed = parse_url($this->_application->SiteUrl())) {
            $path = $parsed['path'];
        }
        setcookie(self::AUTOLOGIN_COOKIE, $value, $expires, $path, '', false, true);
    }

    public function getManagerPlugin()
    {
        if (!$manager_name = $this->getConfig('userManagerPlugin')) {
            throw new Plugg_Exception('User manager plugin is not defined.');
        }

        return $this->_application->getPlugin($manager_name);
    }

    public function getIdentityFetcher()
    {
        if (!isset($this->_identityFetcher)) $this->_identityFetcher = new Plugg_User_IdentityFetcher($this);

        return $this->_identityFetcher;
    }

    public function getIdentity($userId, $withData = false)
    {
        return $this->getIdentityFetcher()->fetchUserIdentity($userId, $withData);
    }

    public function onPluggRun($controller)
    {
        $controller->prependFilter(new Sabai_Handle_Instance(new Plugg_User_Filter()));
    }
    
    public function onUserInstalled($pluginEntity)
    {
        // Create the default fieldset
        if (!$fieldset_field = $this->_application->getPlugin('Form')->getFieldsetField()) {
            return false; // this should never happen but just in case
        }
        
        $field = $this->getModel()->create('Field');
        $field->field_id = $fieldset_field->id;
        $field->markNew();
        $field->name = 'default';
        $field->collapsible = 1;
        $field->settings = serialize(array());
        $field->commit();
    }

    public function onUserPluginConfigured($pluginEntity, $originalParams)
    {
        $params = $pluginEntity->getParams();
        if (array_key_exists('userManagerPlugin', $params)
            && $params['userManagerPlugin'] !== $originalParams['userManagerPlugin']
        ) {
            $this->_application->DispatchEvent(
                'UserManagerPluginChanged',
                array($originalParams['userManagerPlugin'], $params['userManagerPlugin'])
            );
        }
    }
    
    public function onUserAuthenticatorInstalled($pluginEntity, $plugin)
    {
        if ($auths = $plugin->userAuthGetName()) {
            $this->_createPluginUserAuths($pluginEntity->name, $auths);
            $this->clearAllCache();
        }
    }

    public function onUserAuthenticatorUninstalled($pluginEntity, $plugin)
    {
        $this->_deletePluginUserFeature($pluginEntity->name, 'Auth');
        $this->clearAllCache();
    }

    public function onUserAuthenticatorUpgraded($pluginEntity, $plugin)
    {
        if (!$auths = $plugin->userAuthGetName()) {
            $this->_deletePluginUserFeature($pluginEntity->name, 'Auth');
        } else {
            $auths_already_installed = array();
            foreach ($this->getModel()->Auth->criteria()->plugin_is($pluginEntity->name)->fetch() as $current_auth) {
                if (in_array($current_auth->name, $auths)) {
                    $auths_already_installed[] = $current_auth->name;
                } else {
                    $current_auth->markRemoved();
                }
            }
            $this->_createPluginUserAuths($pluginEntity->name, array_diff($auths, $auths_already_installed));
        }

        $this->clearAllCache();
    }

    private function _createPluginUserAuths($pluginName, $auths)
    {
        $model = $this->getModel();
        foreach ($auths as $auth_name => $auth_title) {
            if (empty($auth_name)) continue;
            $auth = $model->create('Auth');
            $auth->name = $auth_name;
            $auth->title = $auth_title;
            $auth->plugin = $pluginName;
            $auth->active = 1;
            $auth->markNew();
        }
        return $model->commit();
    }

    public function onUserWidgetInstalled($pluginEntity, $plugin)
    {
        if ($widgets = $plugin->userWidgetGetNames()) {
            $model = $this->getModel();
            $this->_createPluginUserWidgets($model->create('Widget'), $pluginEntity->name, $widgets);
            $model->commit();
            $this->clearAllCache();
        }
    }

    public function onUserWidgetUninstalled($pluginEntity, $plugin)
    {
        $this->_deletePluginUserFeature($pluginEntity->name, 'Widget');
        $this->clearAllCache();
    }

    public function onUserWidgetUpgraded($pluginEntity, $plugin)
    {
        if (!$widgets = $plugin->userWidgetGetNames()) {
            $this->_deletePluginUserFeature($pluginEntity->name, 'Widget');
        } else {
            $model = $this->getModel();
            $widgets_already_installed = array();
            foreach ($model->Widget->criteria()->plugin_is($pluginEntity->name)->fetch() as $current_widget) {
                if (in_array($current_widget->name, $widgets)) {
                    $widgets_already_installed[] = $current_widget->name;
                    if ($type = @$widgets[$current_widget->name]) {
                        $current_widget->type = $type; // Update the widget type if configured explicitly
                    }
                } else {
                    $current_widget->markRemoved();
                }
            }
            $this->_createPluginUserWidgets(
                $model->create('Widget'),
                $pluginEntity->name,
                array_diff($widgets, $widgets_already_installed)
            );
            $model->commit();
        }

        $this->clearAllCache();
    }

    private function _createPluginUserWidgets($prototype, $pluginName, $widgets)
    {
        foreach ($widgets as $widget_name => $widget_type) {
            if (empty($widget_name)) continue;
            $widget = clone $prototype;
            $widget->name = $widget_name;
            $widget->plugin = $pluginName;
            if (!$widget_type & self::WIDGET_TYPE_PRIVATE) {
                // Make sure the widget is of type public
                $widget_type = $widget_type | self::WIDGET_TYPE_PUBLIC;
            }
            $widget->type = $widget_type;
            $widget->markNew();
            
            // Activate widget
            $widget_plugin = $this->_application->getPlugin($pluginName);
            $active_widget = $widget->createActivewidget();
            $active_widget->title = $widget_plugin->userWidgetGetTitle($widget_name);
            $settings = array();
            if ($widget_settings = $widget_plugin->userWidgetGetSettings($widget_name)) {
                foreach ($widget_settings as $k => $setting) {
                    if (isset($setting['#default_value'])) {
                        $settings[$k] = $setting['#default_value'];
                    }
                }
            }
            $active_widget->settings = serialize($settings);
            $active_widget->position = self::WIDGET_POSITION_LEFT;
            $active_widget->order = 99;
            $active_widget->private = $widget_type & self::WIDGET_TYPE_PRIVATE ? 1 : 0;
            $active_widget->markNew();
        }
    }
    
    public function onUserPermissionableInstalled($pluginEntity, $plugin)
    {
        if (($permissions = $plugin->userPermissionableGetDefaultPermissions())
            && ($role = $this->getModel()->Role->criteria()->system_is(1)->name_is('default')->fetch()->getFirst())
        ) {
            $role->addPermission($plugin->name, $permissions);
            $role->commit();
            
            $this->clearAllCache();
        }
    }

    private function _deletePluginUserFeature($pluginName, $featureName)
    {
        $model = $this->getModel();
        foreach ($model->$featureName->criteria()->plugin_is($pluginName)->fetch() as $entity) {
            $entity->markRemoved();
        }
        return $model->commit();
    }

    public function clearAllCache()
    {
        $this->_getCacheObject()->clean();
    }
    
    /* Start implementation of Plugg_User_Permissionable */

    public function userPermissionableGetPermissions()
    {
        $ret = array(
            'user profile view any' => $this->_("View other user's profile"),
            'user profile edit own' => $this->_('Edit own user profile'),
            'user profile edit any' => $this->_("Edit other user's profile"),
            'user image edit own' => $this->_('Edit own user image'),
            'user image edit any' => $this->_("Edit other user's image"),
            'user status edit any' => $this->_("Edit other user's status message"),
            'user account edit any' => $this->_("Edit other user's account settings"),
            'user account email edit own' => $this->_('Edit own user account email'),
            'user account email edit any' => $this->_("Edit other user's account email"),
            'user account password edit own' => $this->_('Edit own user account password'),
            'user account password edit any' => $this->_("Edit other user's account password"),
            'user account delete own' => $this->_('Delete own user account'),
            'user account delete any' => $this->_("Delete other user's account"),
            'user widget edit own' => $this->_('Edit layout and settings of own user widgets'),
            'user widget edit any' => $this->_("Edit layout and settings of other user's user widgets"),
            'user widget view any private' => $this->_("View other user's private widget contents"),
        );
        if ($this->getConfig('allowViewAnyUser')) unset($ret['user profile view any']);
        
        return $ret;
    }

    public function userPermissionableGetDefaultPermissions()
    {
        return array(
            'user profile view any',
            'user profile edit own',
            'user image edit own',
            'user account email edit own',
            'user account password edit own',
            'user widget edit own',
        );
    }
    
    /* End implementation of Plugg_User_Permissionable */

    /* Start implementation of Plugg_Widgets_Widget*/

    public function widgetsGetWidgetNames()
    {
        return array(
            'user_menu' => Plugg_Widgets_Plugin::WIDGET_TYPE_REQUIRE_AUTHENTICATED,
            'login' => Plugg_Widgets_Plugin::WIDGET_TYPE_CACHEABLE | Plugg_Widgets_Plugin::WIDGET_TYPE_REQUIRE_ANONYMOUS
        );
    }

    public function widgetsGetWidgetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'user_menu':
                return $this->_('Your account');
            case 'login':
                return $this->_('Login');
        }
    }

    public function widgetsGetWidgetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'user_menu':
                return $this->_('Displays user account menu when the viewer is logged in.');
            case 'login':
                return $this->_('Displays a user login form when the viewer is not logged in.');
        }
    }

    public function widgetsGetWidgetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'user_menu':
                return array(
                    'menus' => array(
                        '#type' => 'user_select_menu',
                        '#title' => $this->_('Extra menu items'),
                        '#default_value' => @$currentValues['menus'],
                    )
                );
        }

        return array();
    }

    public function widgetsGetWidgetContent($widgetName, $settings, Sabai_User $user)
    {
        switch ($widgetName) {
            case 'user_menu':
                if (!$this->_application->getUser()->isAuthenticated()) return false;
                
                $vars = !empty($settings['menus']) ? array('menus' => $this->_renderMenus($settings['menus'], $user)) : array();
                
                return array(
                    'content' => $this->_application->RenderTemplate('user_widget_user_menu', $vars, $this->_name)
                );
                
            case 'login':
                if ($this->_application->getUser()->isAuthenticated()) return false;
                
                $links = array(
                    array(
                        $this->_application->Url('/user/login', array('return' => 1)),
                        $this->_('Login here if you already have an account.'),
                        $this->_('Login')
                    ),
                    array(
                        $this->_application->Url('/user/request_password'),
                        $this->_('Forgotten password? Request new password here.'),
                        $this->_('Request password')
                    ),
                    array(
                        $this->_application->Url('/user/register'),
                        $this->_('New to this website? Register here now!'),
                        $this->_('Create account')
                    )
                );
                $ret = array('<ul>');
                foreach ($links as $link) {
                    $ret[] = sprintf('<li><a href="%s" title="%s">%s</a></li>', $link[0], $link[1], $link[2]);
                }
                $ret[] = '</ul>';

                return array('content' => implode(PHP_EOL, $ret));
        }
    }

    private function _renderMenus(array $menus, Sabai_User $user)
    {
        $ret = array();
        foreach ($menus as $menu) {
            // Plugin and menu names are concatenated by an underscore
            list($plugin_name, $menu_name) = explode('_', $menu);
            if (!isset($_SESSION['Plugg_User_Plugin'][$plugin_name][$menu_name])) {
                if (($plugin = $this->_application->getPlugin($plugin_name)) &&
                    ($link_text = $plugin->userMenuGetLinkText($menu_name, $user))
                ) {
                    $_SESSION['Plugg_User_Plugin'][$plugin_name][$menu_name] = array(
                        'text' => $link_text,
                        'url' => $plugin->userMenuGetLinkUrl($menu_name, $user),
                        'plugin' => $plugin->nicename
                    );
                } else {
                    $_SESSION['Plugg_User_Plugin'][$plugin_name][$menu_name] = false;
                }
            }
            $ret[] = $_SESSION['Plugg_User_Plugin'][$plugin_name][$menu_name];
        }

        return $ret;
    }

    public function clearMenuInSession($pluginName, $menuName)
    {
        unset($_SESSION['Plugg_User_Plugin'][$pluginName][$menuName]);
    }

    /* End implementation of Plugg_Widgets_Widget*/

    /* Start implementation of Plugg_AdminWidget_Widget*/

    public function adminWidgetGetNames()
    {
        return array(
            'new_users' => Plugg_AdminWidget_Plugin::WIDGET_TYPE_CACHEABLE,
            'user_logins' => Plugg_AdminWidget_Plugin::WIDGET_TYPE_CACHEABLE,
            'user_updates' => Plugg_AdminWidget_Plugin::WIDGET_TYPE_CACHEABLE,
        );
    }

    public function adminWidgetGetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'new_users':
                return $this->_('New users');
            case 'user_logins':
                return $this->_('User logins');
            case 'user_updates':
                return $this->_('User updates');
        }
    }

    public function adminWidgetGetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'new_users':
                return $this->_('Displays recently created user accounts.');
            case 'user_logins':
                return $this->_('Displays recent user logins.');
            case 'user_updates':
                return $this->_('Displays recent user profile updates.');
        }
    }

    public function adminWidgetGetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'new_users':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of entries to display'),
                        '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10),
                        '#delimiter' => '&nbsp;',
                        '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 5,
                    ),
                );
            case 'user_logins':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of entries to display'),
                        '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10),
                        '#delimiter' => '&nbsp;',
                        '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 5,
                    ),
                );
            case 'user_updates':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of entries to display'),
                        '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10),
                        '#delimiter' => '&nbsp;',
                        '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 5,
                    ),
                    'type' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Default edit type:'),
                        '#options' => array(
                            'profile' => $this->_('Profile'),
                            'email' => $this->_('Email'),
                            'password' => $this->_('Password'),
                        ),
                        '#delimiter' => '<br />',
                        '#default_value' => isset($currentValues['type']) ? $currentValues['type'] : 'profile',
                    ),
                );
        }

        return array();
    }

    public function adminWidgetGetContent($widgetName, $widgetSettings)
    {
        switch ($widgetName) {
            case 'new_users':
                return $this->_renderNewUsersAdminWidget($widgetSettings);
            case 'user_logins':
                return $this->_renderUserLoginsAdminWidget($widgetSettings);
            case 'user_updates':
                return $this->_renderUserUpdatesAdminWidget($widgetSettings);
        }
    }

    private function _renderNewUsersAdminWidget($settings)
    {
        $data = array();
        $users = $this->getIdentityFetcher()->fetchIdentities($settings['limit'], 0, 'timestamp', 'DESC');
        foreach ($users as $user) {
            if ($user->isAnonymous()) continue;
            $data['content'][] = array(
                'title' => $user->display_name,
                'url' => $user_url = $this->_application->User_IdentityUrl($user),
                'thumbnail' => $user->image_thumbnail,
                'thumbnail_link' => $user_url,
                'thumbnail_title' => $user->display_name,
                'timestamp' => $user->created,
            );
        }

        return $data;
    }

    private function _renderUserLoginsAdminWidget($settings)
    {
        $data = array();
        $stats = $this->getModel()->Stat
            ->criteria()
            ->lastLogin_isGreaterThan(0)
            ->fetch($settings['limit'], 0, 'last_login', 'DESC')
            ->with('User');
        foreach ($stats as $stat) {
            if ($stat->User->isAnonymous()) continue;
            $data['content'][] = array(
                'title' => $stat->User->display_name,
                'url' => $user_url = $this->_application->User_IdentityUrl($stat->User),
                'thumbnail' => $stat->User->image_thumbnail,
                'thumbnail_link' => $user_url,
                'thumbnail_title' => $stat->User->display_name,
                'timestamp' => $stat->last_login,
            );
        }

        return $data;
    }

    private function _renderUserUpdatesAdminWidget($settings)
    {
        $data = array();
        $data['menu'] = array(
            array(
                'text' => $this->_('Profile'),
                'settings' => array('type' => 'profile'),
            ),
            array(
                'text' => $this->_('Email'),
                'settings' => array('type' => 'email'),
            ),
            array(
                'text' => $this->_('Password'),
                'settings' => array('type' => 'password'),
            ),
        );

        $criteria = $this->getModel()->createCriteria('Stat');
        switch ($settings['type']) {
            case 'profile':
                $sort = 'last_edit';
                $data['menu'][0]['current'] = true;
                $criteria = $criteria->lastEdit_isGreaterThan(0);
                break;
            case 'email':
                $sort = 'last_edit_email';
                $data['menu'][1]['current'] = true;
                $criteria = $criteria->lastEditEmail_isGreaterThan(0);
                break;
            case 'password':
                $sort = 'last_edit_password';
                $data['menu'][2]['current'] = true;
                $criteria = $criteria->lastEditPassword_isGreaterThan(0);
                break;
        }

        $stats = $this->getModel()->Stat
            ->fetchByCriteria($criteria, $settings['limit'], 0, $sort, 'DESC')
            ->with('User');
        foreach ($stats as $stat) {
            if ($stat->User->isAnonymous()) continue;
            $data['content'][] = array(
                'title' => $stat->User->display_name,
                'url' => $this->_application->User_IdentityUrl($stat->User),
                'thumbnail' => $user_url = $stat->User->image_thumbnail,
                'thumbnail_link' => $user_url,
                'thumbnail_title' => $stat->User->display_name,
                'timestamp' => $stat->$sort,
            );
        }

        return $data;
    }

    /* End implementation of Plugg_AdminWidget_Widget*/

    public function onUserLoginSuccess($user)
    {
        $stat = $this->_getStatByIdentity($user->getIdentity());
        $stat->last_login = time();
        $stat->commit();
    }

    public function onUserLogoutSuccess($user)
    {
        // Invalidate autologin cookie
        $this->_setAutologinCookie('', time() - 3600);
    }

    public function onUserRegisterSuccess(Sabai_User_Identity $identity)
    {
        // Init stat data for the user
        $stat = $this->_getStatByIdentity($identity);
        foreach (array('last_edit', 'last_edit_email', 'last_edit_password', 'last_edit_image') as $stat_type) {
            $stat->$stat_type = time();
        }
        $stat->commit();

        // Send user registration complete email
        $mail_sender = $this->_application->getPlugin('Mail')->getSender();
        $replacements = array(
            '{SITE_NAME}' => $site_name = $this->_application->SiteName(),
            '{SITE_URL}' => $this->_application->SiteUrl(),
            '{USER_NAME}' => $identity->username,
            '{USER_EMAIL}'=> $identity->email,
            '{LOGIN_LINK}' => $this->getUrl(),
            '{IP}' => getip(),
        );
        // Send to site admin
        $subject = strtr($this->getConfig('register_complete_admin_email_subject'), $replacements);
        $body = strtr($this->getConfig('register_complete_admin_email_body'), $replacements);
        $mail_sender->mailSend($this->_application->SiteEmail(), $subject, $body);

        if ($this->getConfig('register_complete_email', 'enable')) {
            // Send to registered user
            $subject = strtr($this->getConfig('register_complete_email', 'subject'), $replacements);
            $body = strtr($this->getConfig('register_complete_email', 'body'), $replacements);
            $mail_sender->mailSend($identity->email, $subject, $body);
        }
    }

    public function onUserIdentityEditSuccess(Sabai_User_Identity $identity)
    {
        $stat = $this->_getStatByIdentity($identity);
        $stat->last_edit = time();
        $stat->commit();
    }

    public function onUserIdentityEditEmailSuccess(Sabai_User_Identity $identity)
    {
        $stat = $this->_getStatByIdentity($identity);
        $stat->last_edit_email = time();
        $stat->commit();
    }

    public function onUserIdentityEditPasswordSuccess(Sabai_User_Identity $identity)
    {
        $stat = $this->_getStatByIdentity($identity);
        $stat->last_edit_password = time();
        $stat->commit();
    }

    public function onUserIdentityEditImageSuccess(Sabai_User_Identity $identity)
    {
        $stat = $this->_getStatByIdentity($identity);
        $stat->last_edit_image = time();
        $stat->commit();
    }

    private function _getStatByIdentity(Sabai_User_Identity $identity)
    {
        $id = $identity->id;
        $model = $this->getModel();
        if (!$stat = $model->Stat->fetchByUser($id)->getFirst()) {
            $stat = $model->create('Stat');
            $stat->user_id = $id;
            $stat->markNew();
        }
        return $stat;
    }

    public function onUserIdentityDeleteSuccess($identity)
    {
        $id = $identity->id;
        $model = $this->getModel();
        $model->getGateway('Stat')->deleteByCriteria($model->createCriteria('Stat')->userId_is($id));
        $model->getGateway('Meta')->deleteByCriteria($model->createCriteria('Meta')->userId_is($id));
        $model->getGateway('Authdata')->deleteByCriteria($model->createCriteria('Authdata')->userId_is($id));
        $model->getGateway('Autologin')->deleteByCriteria($model->createCriteria('Autologin')->userId_is($id));
        $model->getGateway('Queue')->deleteByCriteria($model->createCriteria('Queue')->identityId_is($id));
        $model->getGateway('Activewidget')->deleteByCriteria($model->createCriteria('Activewidget')->userId_is($id));
    }

    public function onPluggCron($lastrun, array $logs)
    {
        // Allow run this cron 1 time per day at most
        if (!empty($lastrun) && time() - $lastrun < 86400) return;

        $model = $this->getModel();
        
        $logs[] = $this->_('Puring expired autologin data.');

        // Remove expired autologin sessions
        $criteria = $model->createCriteria('Autologin')
            ->expires_isSmallerThan(time());
        $model->getGateway('Autologin')->deleteByCriteria($criteria);
        
        $logs[] = $this->_('Deleting user queues older than 3 days.');

        // Remove queues older than 3 days
        $criteria = $model->createCriteria('Queue')
            ->created_isSmallerThan(time() - 259200);
        $model->getGateway('Queue')->deleteByCriteria($criteria);
    }

    public function sendRegisterConfirmEmail($queue, $confirmByAdmin = false)
    {
        $confirm_link = $this->getUrl('confirm/' . $queue->id, array('key' => $queue->key), '&');
        $confirm_recipient = $queue->notify_email;
        $data = $queue->getData();
        $replacements = array(
            '{SITE_NAME}' => $this->_application->SiteName(),
            '{SITE_URL}' => $this->_application->SiteUrl(),
            '{USER_NAME}' => $queue->register_username,
            '{USER_EMAIL}' => $confirm_recipient,
            '{CONFIRM_LINK}' => $confirm_link,
            '{IP}' => getip()
        );
        if ($confirmByAdmin) {
            // Send confirmation mail to admin
            $subject_template = $this->getConfig('register_confirm_admin_email', 'subject');
            $body_template = $this->getConfig('register_confirm_admin_email', 'body');
            $to = $this->_application->SiteEmail();
        } else {
            $subject_template = $this->getConfig('register_confirm_email', 'subject');
            $body_template = $this->getConfig('register_confirm_email', 'body');
            $to = $confirm_recipient;
        }
        $body = strtr($body_template, $replacements);
        $subject = strtr($subject_template, $replacements);

        return $this->_application->getPlugin('Mail')->getSender()->mailSend($to, $subject, $body);
    }

    public function sendEditEmailConfirmEmail($queue)
    {
        $identity = $this->getIdentity($queue->identity_id);
        if ($identity->isAnonymous()) {
            return false;
        }

        $confirm_link = $this->getUrl('confirm/' . $queue->id, array('key' => $queue->key), '&');
        $confirm_email = $queue->notify_email;
        $replacements = array(
            '{SITE_NAME}' => $this->_application->SiteName(),
            '{SITE_URL}' => $this->_application->SiteUrl(),
            '{USER_NAME}' => $identity->username,
            '{USER_EMAIL}' => $confirm_email,
            '{CONFIRM_LINK}' => $confirm_link,
            '{IP}' => getip(),
        );
        $subject = strtr($this->getConfig('edit_email_confirm_email', 'subject'), $replacements);
        $body = strtr($this->getConfig('edit_email_confirm_email', 'body'), $replacements);

        $this->_application->getPlugin('Mail')->getSender()->mailSend($confirm_email, $subject, $body);
    }

    public function sendRequestPasswordConfirmEmail($queue, $identity)
    {
        $confirm_link = $this->getUrl('confirm/' . $queue->id, array('key' => $queue->key), '&');
        $replacements = array(
            '{SITE_NAME}' => $site_name = $this->_application->SiteName(),
            '{SITE_URL}' => $this->_application->SiteUrl(),
            '{USER_NAME}' => $identity->username,
            '{USER_EMAIL}'=> $identity->email,
            '{CONFIRM_LINK}' => $confirm_link,
            '{IP}' => getip(),
        );
        $subject = strtr($this->getConfig('new_password_confirm_email', 'subject'), $replacements);
        $body = strtr($this->getConfig('new_password_confirm_email', 'body'), $replacements);

        return $this->_application->getPlugin('Mail')->getSender()->mailSend($identity->email, $subject, $body);
    }

    public function createAuthdata($authData, $userId)
    {
        $auth_data = $this->getModel()->create('Authdata');
        $auth_data->claimed_id = $authData['claimed_id'];
        $auth_data->display_id = $authData['display_id'];
        //$auth_data->type = $authData['type'];
        $auth_data->lastused = time();
        $auth_data->auth_id = $authData['auth_id'];
        $auth_data->user_id = $userId;
        $auth_data->markNew();
        return $auth_data->commit();
    }

    public function getWidgetData()
    {
        // Fetch available widgets and data
        $widgets = array();
        foreach ($this->getModel()->Widget->fetch(0, 0, 'plugin', 'ASC') as $widget) {
            // skip if plugin of the widget is not enabled
            if (!$widget_plugin = $this->_application->getPlugin($widget->plugin)) continue;
            if (!$widget_plugin instanceof Plugg_User_Widget) continue;

            $widgets[$widget->id] = array(
                'id' => $widget->id,
                'name' => $widget->name,
                'title' => $widget_plugin->userWidgetGetTitle($widget->name),
                'summary' => $widget_plugin->userWidgetGetSummary($widget->name),
                'settings' => $widget_plugin->userWidgetGetSettings($widget->name),
                'plugin' => $widget_plugin->nicename,
                'is_private' => $widget->isType(self::WIDGET_TYPE_PRIVATE),
            );
        }

        return $widgets;
    }

    public function getActiveWidgets($userId = 0)
    {
        return $this->getModel()->Activewidget
            ->criteria()
            ->userId_is($userId)
            ->fetch(0, 0, 'order', 'ASC');
    }

    public function addExtraFormFields(&$form, $identity = null, array $extraFields = array(), array $extraFieldsVisibility = array())
    {
        $editable = $registerable = null;
        if (!empty($identity)) {
            $editable = true;
        } else {
            $registerable = true;
        }
        $fields = $this->getFields($registerable, $editable);   
        if (!empty($fields[0])) {
            // Initialize visibility control field
            $visibility_control = array(
                '#type' => 'select',
                '#options' => $this->_application->User_FieldVisibilities(),
                '#size' => 6,
                '#multiple' => true,
                '#collapsible' => true,
                '#collapsed' => true,
                '#weight' => 99,
            );
            foreach ($fields[0] as $fieldset) {
                if (!empty($fields[$fieldset->id])) {
                    $field_settings = array();
                    foreach (unserialize($fieldset->settings) as $field_setting_key => $field_setting) {
                        $field_settings['#' . $field_setting_key] = $field_setting;
                    }
                    $form[$fieldset->name] = array_merge(
                        $field_settings,
                        array(
                            '#type' => 'fieldset',
                            '#title' => $fieldset->title,
                            '#description' => $fieldset->description,
                            '#weight' => $fieldset->weight,
                            '#collapsible' => $fieldset->collapsible,
                            '#collapsed' => $fieldset->collapsed,
                            '#tree' => false,
                        )
                    );
                    foreach ($fields[$fieldset->id] as $field) {
                        $field_name = '__User_' . $field->name;
                        $form[$fieldset->name][$field_name] = array(
                            '#tree' => true,
                            '#collapsible' => true,
                        );
                        $field_settings = array();
                        foreach (unserialize($field->settings) as $field_setting_key => $field_setting) {
                            $field_settings['#' . $field_setting_key] = $field_setting;
                        }
                        $form[$fieldset->name][$field_name]['value'] = array_merge(
                            $field_settings,
                            array(
                                '#type' => $field->FormField->type,
                                '#title' => $field->title,
                                '#description' => $field->description,
                                '#weight' => $field->weight,
                                '#required' => $field->required,
                                '#default_value' => @$extraFields[$field->name],
                            )
                        );
                        if ($field->visibility_control) {
                            // Init visibility control field for the user field
                            $visibility_control_copy = $visibility_control;
                            $visibility_control_copy['#title'] = sprintf($this->_('Display %s to:'), $field->title);
                            if ($visibility = @$extraFieldsVisibility[$field->name]) {
                                $visibility_control_copy['#default_value'] = $visibility;
                            } else {
                                $visibility_control_copy['#default_value'] = array('@all');
                            }
                            $form[$fieldset->name][$field_name]['visibility'] = $visibility_control_copy;
                            
                            $form[$fieldset->name][$field_name]['#title'] = $field->title;
                            unset($form[$fieldset->name][$field_name]['value']['#title']);
                        }
                    }
                }
            }
        }
    }

    public function extractExtraFormFieldValues($formValues)
    {
        $ret = array('value' => array(), 'visibility' => array());
        $fields = $this->getFields();
        if (!empty($fields[0])) {
            foreach ($fields[0] as $fieldset) {
                if (!empty($fields[$fieldset->id])) {
                    foreach ($fields[$fieldset->id] as $field) {
                        $field_type = $field->FormField->type;
                        $field_name = '__User_' . $field->name;
                        $field_value = @$formValues[$field_name]['value'];
                        $field_settings = unserialize($field->settings);
                        $ret['value'][$field->name] = $field_value;
                        $ret['visibility'][$field->name] = $field->visibility_control ? (array)@$formValues[$field_name]['visibility'] : array();
                    }
                }
            }
        }

        return $ret;
    }
    
    public function createDefaultFieldset($commit = false)
    {
        // Create the default fieldset
        if (!$fieldset_field = $this->_application->getPlugin('Form')->getFieldsetField()) {
            return false; // this should never happen but just in case
        }
        
        $fieldset = $this->getModel()->create('Field');
        $fieldset->markNew();
        $fieldset->field_id = $fieldset_field->id;
        $fieldset->name = 'default';
        $fieldset->settings = serialize(array());
        
        if ($commit) return $fieldset->commit() ? $fieldset : false;
        
        return $fieldset;
    }
    
    public function getFields($registerable = null, $editable = null)
    {
        if (!$fieldset_field = $this->_application->getPlugin('Form')->getFieldsetField()) {
            // this should never happen but just in case
            return false;
        }
        
        $fields = array();
        $criteria1 = $this->getModel()->createCriteria('Field');
        $criteria1->fieldset_is(0);
        $criteria2 = $this->getModel()->createCriteria('Field');
        if (isset($registerable)) $criteria2->registerable_is(intval($registerable));
        if (isset($editable)) $criteria2->editable_is(intval($editable));
        $criteria = Sabai_Model_Criteria::createComposite(array($criteria1, $criteria2), Sabai_Model_Criteria::CRITERIA_OR);
        foreach ($this->getModel()->Field->fetchByCriteria($criteria, 0, 0, 'weight', 'ASC')->with('FormField') as $field) {
            if ($field->fieldset == 0) {
                if ($field->field_id != $fieldset_field->id) {
                    // This field does not have a parent fieldset but not a fieldset
                    $fields_no_parent[] = $field;
                    continue;
                }
                // Is it the default fieldset?
                if ($field->name == 'default') $default_fieldset = $field;
            }
            $fields[$field->fieldset][] = $field;
        }
        // Place formfields without parent under the default fieldset
        if (!empty($fields_no_parent)) {
            if (empty($default_fieldset)) {
                if (!$default_fieldset = $this->createDefaultFieldset(true)) {
                    return false;
                }
            }
            foreach ($fields_no_parent as $field_no_parent) {
                $fields[$default_fieldset->id][] = $field_no_parent;
            }
        }
        
        return $fields;
    }
    
    public function getPermissions()
    {
        if ($cached = $this->getCache('permissions')) {
            if (false !== $ret = @unserialize($cached)) return $ret;
        }
        
        $permissions = $permissions_default = array();
        $permissionables = $this->_application->getPluginManager()->getInstalledPluginsByInterface('Plugg_User_Permissionable');
        foreach (array_keys($permissionables) as $plugin_name) {
            if (!$permissionable = $this->_application->getPlugin($plugin_name)) continue;
        
            $permissions[$plugin_name] = $permissionable->userPermissionableGetPermissions();
            $permissions_default = $permissions_default + $permissionable->userPermissionableGetDefaultPermissions();
        }    
        ksort($permissions, SORT_LOCALE_STRING);
        $permissions_default = array_unique($permissions_default);
        $permissions_arr = array($permissions, $permissions_default);
        $this->saveCache(serialize($permissions_arr), 'permissions');
        
        return $permissions_arr;
    }
    
    public function getDefaultConfig()
    {
        return array(
            'userManagerPlugin' => '',
            'allowViewAnyUser' => false,
            'allowRegistration' => true,
            'userActivation' => 'user',
            'enableAutologin' => true,
            'autologinSessionLifetime' => 3,
            'limitSingleAutologinSession' => true,
            'register_confirm_email' => array(
                'subject' => $this->_('User registration confirmation for {USER_NAME}@{SITE_NAME}'),
                'body' => implode(PHP_EOL . PHP_EOL, array(
                    $this->_('Hello {USER_NAME},'),
                    $this->_('The email address ({USER_EMAIL}) has been used to register an account at {SITE_NAME}.'),
                    $this->_('To become a member of {SITE_NAME}, please confirm your request by clicking on the link below:'),
                    '{CONFIRM_LINK}',
                    '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}'
                )),
            ),
            'register_complete_email' => array(
                'enable' => true,
                'subject' => $this->_('Account details for {USER_NAME}@{SITE_NAME}'),
                'body' => implode(PHP_EOL . PHP_EOL, array(
                    $this->_('Hello {USER_NAME},'),
                    $this->_('Your user account registration at {SITE_NAME} is complete.'),
                    $this->_('You can now login from the following URL with the password you have provided upon registration:'),
                    '{LOGIN_LINK}',
                    '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}',
                )),
            ),
            'edit_email_confirm_email' => array(
                'subject' => $this->_('Email modification confirmation for {USER_NAME}@{SITE_NAME}'),
                'body' => implode(PHP_EOL . PHP_EOL, array(
                    $this->_('Hello {USER_NAME},'),
                    $this->_('The email address ({USER_EMAIL}) has been used for a user account at {SITE_NAME}.'),
                    $this->_('Please click on the link below to confirm the email address:'),
                    '{CONFIRM_LINK}',
                    '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}'
                )),
            ),
            'new_password_confirm_email' => array(
                'subject' => $this->_('New user password request for {USER_NAME}@{SITE_NAME}'),
                'body' => implode(PHP_EOL . PHP_EOL, array(
                    $this->_('Hello {USER_NAME},'),
                    $this->_('A web user from {IP} has just requested a new password for your user account at {SITE_NAME}.'),
                    $this->_('Please click on the link below to confirm the request and receive a new password:'),
                    '{CONFIRM_LINK}',
                    $this->_('If you did not ask for this, you can just ignore this email.'),
                    '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}'
                )),
            ),
            'new_password_email' => array(
                'subject' => $this->_('New user password for {USER_NAME}@{SITE_NAME}'),
                'body' => implode(PHP_EOL . PHP_EOL, array(
                    $this->_('Hello {USER_NAME},'),
                    $this->_('A web user from {IP} has just requested a new password for your user account at {SITE_NAME}.'),
                    $this->_('Here are your login details:'),
                    $this->_('Username: {USER_NAME}') . PHP_EOL . $this->_('New Password: {USER_PASSWORD}'),
                    $this->_('You can change the password after you login from the following URL:'),
                    '{LOGIN_LINK}',
                    '-----------' . PHP_EOL . '{SITE_NAME}' . PHP_EOL . '{SITE_URL}'
                )),
            )
        );
    }
}
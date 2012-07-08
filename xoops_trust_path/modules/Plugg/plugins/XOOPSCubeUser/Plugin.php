<?php
class Plugg_XOOPSCubeUser_Plugin extends Plugg_Plugin implements Plugg_User_Manager_ApplicationWithImage
{
    const FIELD_VIEWABLE = 1, FIELD_EDITABLE = 2, FIELD_REGISTERABLE = 4;

    private $_xoopsDB, $_xoopsUrl;

    public function userLoginGetForm(array $form)
    {
        return $form;
    }

    public function userLoginSubmitForm(Plugg_Form_Form $form)
    {
        $db = $this->getXoopsDB();
        $sql = sprintf(
            'SELECT * FROM %susers WHERE uname = %s AND pass = %s',
            $db->getResourcePrefix(), $db->escapeString($form->values['username']), $db->escapeString(md5($form->values['password']))
        );
        if (($rs = $db->query($sql, 1, 0)) && ($row = $rs->fetchAssoc())) {
            return $this->_buildIdentity($row);
        }
        $form->setError($this->_('Invalid user name or password.'), array('username', 'password'));

        return false;
    }

    public function userLoginUser(Sabai_User $user)
    {
        $app_id = $this->_application->getId();
        if (empty($_SESSION[__CLASS__][$app_id]['keepalive'])) {
            session_regenerate_id();
            register_shutdown_function(array($this, 'shutdown'), $user);
            $_SESSION[__CLASS__][$app_id]['keepalive'] = true;
        }

        return true;
    }

    public function userLogoutUser(Sabai_User $user)
    {
        $app_id = $this->_application->getId();
        unset($_SESSION[__CLASS__][$app_id]['keepalive']);

        return true;
    }

    public function userGetCurrentUser()
    {
        $app_id = $this->_application->getId();
        if (!isset($_SESSION[__CLASS__][$app_id]['user'])) {
            unset($_SESSION[__CLASS__][$app_id]);

            return false;
        }

        if (!$user = unserialize($_SESSION[__CLASS__][$app_id]['user'])) {
            unset($_SESSION[__CLASS__][$app_id]);

            return false;
        }
        register_shutdown_function(array($this, 'shutdown'), $user);

        return $user;
    }

    public function shutdown($user)
    {
        $app_id = $this->_application->getId();
        if ($user->isAuthenticated() && !empty($_SESSION[__CLASS__][$app_id]['keepalive'])) {
            // Finalize?
            if ($user->finalize()) {
                $user->setFinalized();
                $user->finalize(false);
            }
            $_SESSION[__CLASS__][$app_id]['user'] = serialize($user);
        } else {
            unset($_SESSION[__CLASS__][$app_id]['user'], $_SESSION[__CLASS__][$app_id]['keepalive']);
        }
    }

    public function userGetAnonymousIdentity()
    {
        return new Plugg_User_AnonymousIdentity($GLOBALS['xoopsConfig']['anonymous']);
    }

    public function userRegisterGetForm($username = null, $email = null, $name = null)
    {
        $settings = array();

        $builder = new Plugg_XOOPSCubeUser_RegisterFormBuilder($this);
        $builder->buildForm($settings);

        if (isset($username)) $settings['uname']['#default_value'] = $username;
        if (isset($email)) $settings['email']['#default_value'] = $settings['email_confirm']['#default_value'] = $email;
        if (isset($name)) $settings['name']['#default_value'] = $name;

        return $settings;
    }

    public function userRegisterQueueForm(Plugg_User_Model_Queue $queue, Plugg_Form_Form $form)
    {
        $data = array('uname' => $form->values['uname'], 'email' => $form->values['email'], 'pass' => md5($form->values['pass']));
        foreach (array('name', 'url', 'user_avatar', 'user_icq', 'user_from', 'user_sig',
                       'user_viewemail', 'user_aim', 'user_yim', 'user_msnm', 'attachsig', 'rank', 'theme',
                       'timezone_offset', 'umode', 'uorder', 'notify_method', 'notify_mode', 'user_occ',
                       'bio', 'user_intrest', 'user_mailok') as $var_name) {
            if (array_key_exists($var_name, $form->values)) $data[$var_name] = $form->values[$var_name];
        }
        $queue->setData($data);
        $queue->notify_email = $form->values['email'];
        $queue->register_username = $form->values['uname'];

        return true;
    }

    public function userRegisterSubmit(Plugg_User_Model_Queue $queue)
    {
        $data = $queue->getData();

        if ($this->isUnameRegistered($data['uname'])) {
            return $this->_('The username is already registered.');
        }
        if ($this->isEmailRegistered($data['email'])) {
            return $this->_('The email address is already registered.');
        }

        $db = $this->getXoopsDB();
        $values = $this->_toSqlData($db, $data);
        ksort($values);
        $columns = array_keys($values);
        sort($columns);
        $db->beginTransaction();
        $sql = sprintf(
            'INSERT INTO %susers (%s) VALUES (%s)',
            $db->getResourcePrefix(), implode(',', $columns), implode(',', $values)
        );
        if ($db->exec($sql)) {
            $user_id = $db->lastInsertId($db->getResourcePrefix() . 'users', 'uid');
            $sql = sprintf(
                'INSERT INTO %sgroups_users_link (groupid, uid) VALUES (%d, %d)',
                $db->getResourcePrefix(), $this->getXoopsUsersGroupId(), $user_id
            );
            if ($db->exec($sql)) {
                $db->commit();
                $sql = sprintf('SELECT * FROM %susers WHERE uid = %d', $db->getResourcePrefix(), $user_id);
                if (($rs = $db->query($sql, 1, 0)) && ($row = $rs->fetchAssoc())) {
                    return $this->_buildIdentity($row);
                }
            }
        }
        $db->rollback();

        return false;
    }

    public function userEditGetForm(Sabai_User_Identity $identity)
    {
        $settings = $current_values = array();
        
        // Retrieve current data for this identity
        $db = $this->getXoopsDB();
        $sql = sprintf('SELECT * FROM %susers WHERE uid = %d', $db->getResourcePrefix(), $identity->id);
        if ($rs = $db->query($sql, 1, 0)) {
            $current_values = $rs->fetchAssoc();
        }
        $builder = new Plugg_XOOPSCubeUser_EditFormBuilder($this);
        $builder->buildForm($settings, $current_values);

        return $settings;
    }

    public function userEditSubmitForm(Sabai_User_Identity $identity, Plugg_Form_Form $form)
    {
        $db = $this->getXoopsDB();
        $data = $this->_toSqlData($db, $form->values);

        // Some values should never be edited here
        unset($data['uname'], $data['pass'], $data['email'], $data['user_avatar'], $data['user_regdate']);

        // Return current identity if no data to update
        if (empty($data)) return $identity;

        $sets = array();
        foreach (array_keys($data) as $column) {
            $sets[] = $column . '=' . $data[$column];
        }
        $sql = sprintf('UPDATE %susers SET %s WHERE uid = %d', $db->getResourcePrefix(), implode(',', $sets), $identity->id);

        return !$db->exec($sql) ? false : $identity;
    }

    private function _toSqlData($db, $values)
    {
        $int_fields = array(
            'user_regdate' => time(), 'user_viewemail' => 0, 'attachsig' => 0,
            'rank' => 0, 'level' => 1, 'uorder' => 0, 'notify_method' => 1,
            'notify_mode' => 0, 'user_mailok' => 0
        );
        $string_fields = array(
            'name' => '', 'uname' => '', 'email' => '', 'url' => '', 'user_avatar' => 'blank.gif',
            'user_icq' => '', 'user_from' => '', 'user_sig' => '', 'user_aim' => '',
            'user_yim' => '', 'user_msnm' => '', 'pass' => '', 'theme' => '', 'umode' => '',
            'user_occ' => '', 'bio' => '', 'user_intrest' => ''
        );
        $float_fields = array('timezone_offset' => 0.0);
        $data= array();
        foreach ($int_fields as $field => $default) {
            $data[$field] = isset($values[$field]) ? intval($values[$field]) : $default;
        }
        foreach ($string_fields as $field => $default) {
            $data[$field] = isset($values[$field]) ? $db->escapeString($values[$field]) : $db->escapeString($default);
        }
        foreach ($float_fields as $field => $default) {
            $data[$field] = isset($values[$field]) ? floatval($values[$field]) : $default;
        }
        return $data;
    }

    public function userDeleteSubmit(Sabai_User_Identity $identity)
    {
        $db = $this->getXoopsDB();
        $db->beginTransaction();
        $sql = sprintf('DELETE FROM %susers WHERE uid = %d', $db->getResourcePrefix(), $identity->id);
        if (!$db->exec($sql)) {
            $db->rollback();
            return false;
        }
        $sql = sprintf('DELETE FROM %sgroups_users_link WHERE uid = %d', $db->getResourcePrefix(), $identity->id);
        if (!$db->exec($sql)) {
            $db->rollback();
            return false;
        }
        return $db->commit();
    }

    public function userRequestPasswordGetForm(array $form)
    {
        // Add validation callback to the email field
        $form['email']['#element_validate'][] = array($this, 'userRequestPasswordCheckEmail');

        return $form;
    }

    public function userRequestPasswordCheckEmail(Plugg_Form_Form $form, $value, $name)
    {
        if (!$this->isEmailRegistered($value)) {
            $form->setError($this->_('The email address is not registered.'), $name);
        }
    }

    public function userRequestPasswordQueueForm(Plugg_User_Model_Queue $queue, Plugg_Form_Form $form)
    {
        if (!$queue->identity_id = $this->isEmailRegistered($form->values['email'])) {
            return false;
        }

        $queue->notify_email = $form->values['email'];

        return true;
    }

    public function userRequestPasswordSubmit(Plugg_User_Model_Queue $queue)
    {
        $db = $this->getXoopsDB();
        $new_password = substr(md5(uniqid(mt_rand(), true)), 5, 8);
        $sql = sprintf('UPDATE %susers SET pass = %s WHERE uid = %d', $db->getResourcePrefix(), $db->escapeString(md5($new_password)), $queue->identity_id);
        return $db->exec($sql) ? $new_password : false;
    }

    public function userEditEmailGetForm(Sabai_User_Identity $identity, array $form)
    {
        $form['#validate'][] = array(array($this, 'userEditEmailValidate'), array($identity));

        return $form;
    }

    public function userEditEmailValidate($form, $identity)
    {
        // Make sure the email is not already registered by another user
        if (($identity_id = $this->isEmailRegistered($form->values['email'])) && $identity_id != $identity->id) {
            $form->setError($this->_('The email address is already registered by another user'), 'emails');
        }
    }

    public function userEditEmailQueueForm(Plugg_User_Model_Queue $queue, Sabai_User_Identity $identity, Plugg_Form_Form $form)
    {
        $queue->notify_email = $form->values['email'];

        return true;
    }

    public function userEditEmailSubmit(Plugg_User_Model_Queue $queue, Sabai_User_Identity $identity)
    {
        // Make sure the email is still not registered by another user
        $email = $queue->notify_email;
        if (($identity_id = $this->isEmailRegistered($email)) && $identity_id != $identity->id) {
            return false;
        }

        $db = $this->getXoopsDB();
        $sql = sprintf('UPDATE %susers SET email = %s WHERE uid = %d', $db->getResourcePrefix(), $db->escapeString($email), $queue->identity_id);
        return $db->exec($sql);
    }

    public function userEditPasswordGetForm(Sabai_User_Identity $identity, array $form)
    {
        return $form;
    }

    public function userEditPasswordSubmitForm(Sabai_User_Identity $identity, Plugg_Form_Form $form)
    {
        $db = $this->getXoopsDB();
        $new_password = $form->values['password'];
        $sql = sprintf('UPDATE %susers SET pass = %s WHERE uid = %d', $db->getResourcePrefix(), $db->escapeString(md5($new_password)), $identity->id);

        return $db->exec($sql);
    }

    public function userEditImageGetForm(Sabai_User_Identity $identity)
    {
        $settings = array();

        if ($image = $identity->image) {
            $settings['current'] = array(
                '#type' => 'item',
                '#title' => $this->_('Current image'),
                '#markup' => sprintf('<img src="%1$s" alt="%1$s" />', $image),
            );
        }
        if ($this->getConfig('avatar', 'allowUpload') && $this->getXoopsUploadPath()) {
            $settings['#header'] = array($this->_('Upload an image file to be used as your avatar or select one from the list.'));
            if (!function_exists('gd_info')) {
                // No GD support, so we need to check the file size and dimension of the uploaded file
                $maxsize_msg = sprintf(
                    $this->_('File size must be smaller than %d kilo bytes, %dx%d pixels.'),
                    $this->getConfig('avatar', 'maxSizeKB'), $this->getConfig('avatar', 'maxWidth'), $this->getConfig('avatar', 'maxHeight')
                );
                $settings['file'] = array(
                    '#type' => 'file',
                    '#title' => $this->_('Upload image file'),
                    '#description' => $maxsize_msg,
                    '#size' => 30,
                    '#max_file_size' => $this->getConfig('avatar', 'maxSizeKB') * 1024,
                    '#max_image_width' => $this->getConfig('avatar', 'maxWidth'),
                    '#max_image_height' => $this->getConfig('avatar', 'maxHeight'),
                );
            } else {
                $settings['file'] = array(
                    '#type' => 'file',
                    '#title' => $this->_('Upload image file'),
                    '#size' => 30,
                );
            }
            // Allow image file extensions only
            $settings['file']['#allowed_extension'] = array('gif', 'jpg', 'jpeg', 'png');
        } else {
            $settings['#header'] = array($this->_('Select an avatar image from the list below.'));
        }
        $db = $this->getXoopsDB();
        $sql = sprintf('SELECT avatar_id, avatar_file, avatar_name FROM %savatar WHERE avatar_display = 1 AND avatar_type = %s ORDER BY avatar_weight ASC', $db->getResourcePrefix(), $db->escapeString('S'));
        $avatar_options = array(0 => $this->_('None'));
        $avatar_selected = 0;
        if ($rs = $db->query($sql)) {
            $upload_url = $this->getXoopsUploadUrl();
            $user_avatar = str_replace($upload_url . '/', '', $identity->image);
            while ($row = $rs->fetchRow()) {
                $avatar_options[$row[0]] = sprintf('<img src="%1$s/%2$s" alt="%3$s" />', $upload_url, $row[1], h($row[2]));
                if ($user_avatar == $row[1]) {
                    $avatar_selected = $row[0];
                }
            }
        }
        $settings['avatar'] = array(
            '#type' => 'radios',
            '#title' => $this->_('Select image'),
            '#options' => $avatar_options,
            '#delimiter' => '&nbsp;&nbsp;',
            '#default_value' => $avatar_selected,
        );

        return $settings;
    }

    public function userEditImageSubmitForm(Sabai_User_Identity $identity, Plugg_Form_Form $form)
    {
        $db = $this->getXoopsDB();
        $db->beginTransaction();
        if ($this->getConfig('avatar', 'allowUpload')
            && ($upload_dir = $this->getXoopsUploadPath())
            && !empty($form->values['file'])
        ) {
            // Use GD to resize image file if the size exceeds the max w/h settings

            $uploader = $this->_application->getLocator()->createService('Uploader', array(
                'filenamePrefix' => 'cavt', // file name prefix,
                'filenameMaxLength' => 30,
                'imageOnly' => true,
                'uploadDir' => $upload_dir
            ));
            $result = $uploader->uploadFile($form->values['file']);
            if (!empty($result['upload_error'])) {
                $form->setError($file['upload_error'], 'file');
                $db->rollback();

                return false;
            }

            // Resize the avatar file if width or height is larger than the maximum value
            $max_w = $this->getConfig('avatar', 'maxWidth');
            $max_h = $this->getConfig('avatar', 'maxHeight');
            if ($result['image_width'] > $max_w || $result['image_height'] > $max_h) {
                try {
                    $image_transform = $this->_application->getLocator()->createService('ImageTransform');
                    $image_transform->load($result['file_path']); // load original file
                    $image_transform->fit($max_w, $max_h);
                    $image_transform->save($upload_dir . '/' . $result['file_name']);
                    $image_transform->free();
                } catch (Plugg_Exception $e) {
                    $form->setError($this->_('Failed resizing uploaded image file.'), 'file');
                    $db->rollback();
                    return false;
                }
            }

            $avatar_file = $result['file_name'];
            $avatar_mime = $result['type'];

            $sql = sprintf(
                'INSERT INTO %savatar(avatar_file, avatar_name, avatar_mimetype, avatar_created, avatar_display, avatar_weight, avatar_type)
                   VALUES(%s, %s, %s, %d, 1, 0, %s)',
                $db->getResourcePrefix(), $db->escapeString($avatar_file), $db->escapeString($identity->username), $db->escapeString($avatar_mime), time(), $db->escapeString('C')
            );
            if (!$db->exec($sql)) {
                $db->rollback();
                return false;
            }
            $avatar_id = $db->lastInsertId($db->getResourcePrefix() . 'avatar', 'avatar_id');
        } elseif ($avatar_id = @$form->values['avatar']) {
            $sql = sprintf('SELECT avatar_file FROM %savatar WHERE avatar_id = %d AND avatar_type = %s', $db->getResourcePrefix(), $avatar_id, $db->escapeString('S'));
            if (($rs = $db->query($sql, 1, 0)) && ($row = $rs->fetchRow())) {
                $avatar_file = $row[0];
            } else {
                // Default avatar image for XCL
                $avatar_file = 'blank.gif';
            }
        }
        if (isset($avatar_id) && !empty($avatar_file)) {
            $sql = sprintf('UPDATE %susers SET user_avatar = %s WHERE uid = %d', $db->getResourcePrefix(), $db->escapeString($avatar_file), $identity->id);
            if (!$db->exec($sql)) {
                $db->rollback();
                return false;
            }
            $sql = sprintf('DELETE FROM %savatar_user_link WHERE user_id = %d', $db->getResourcePrefix(), $identity->id);
            if (!$db->exec($sql, false)) {
                $db->rollback();
                return false;
            }

            if (!empty($avatar_id)) {
                $sql = sprintf('INSERT INTO %savatar_user_link(avatar_id, user_id) VALUES(%d, %d)', $db->getResourcePrefix(), $avatar_id, $identity->id);
                if (!$db->exec($sql)) {
                    $db->rollback();
                    return false;
                }
            }
            $db->commit();

            // Remove unused avatar files
            $sql = sprintf('SELECT avatar_id, avatar_file FROM %savatar WHERE avatar_type = %s AND avatar_name = %s AND avatar_id != %d', $db->getResourcePrefix(), $db->escapeString('C'), $db->escapeString($identity->username), $avatar_id);
            $old_files = array();
            if ($rs = $db->query($sql)) {
                while ($row = $rs->fetchRow()) {
                    $old_files[$row[0]] = $row[1];
                }
            }
            if (!empty($old_files)) {
                $sql = sprintf('DELETE FROM %savatar WHERE avatar_id IN (%s)', $db->getResourcePrefix(), implode(',', array_keys($old_files)));
                if ($db->exec($sql)) {
                    foreach ($old_files as $old_file) {
                        @unlink($this->getXoopsUploadPath() . '/' . $old_file);
                    }
                }
            }

            return true;
        }
        $db->rollback();

        return false;
    }

    public function userViewRenderIdentity(Sabai_User_Identity $identity, array $extraFields)
    {
        $db = $this->getXoopsDB();
        $sql = sprintf('SELECT * FROM %susers WHERE uid = %d', $db->getResourcePrefix(), $identity->id);
        $vars = array(
            'extra_fields' => $extraFields,
            'identity' => $identity,
            'config' => $this->getConfig('fields'),
            'fields' => ($rs = $db->query($sql)) ? $rs->fetchAssoc() : array(),
        );

        return $this->_application->RenderTemplate('xoopscubeuser_user_identity', $vars, $this->_name);
    }

    public function userFetchIdentitiesByIds($userIds)
    {
        $ret = array();
        $db = $this->getXoopsDB();
        $sql = sprintf('SELECT * FROM %susers WHERE uid IN (%s)', $db->getResourcePrefix(), implode(',', array_map('intval', $userIds)));
        if ($rs = $db->query($sql)) {
            while ($row = $rs->fetchAssoc()) {
                $ret[$row['uid']] = $this->_buildIdentity($row);
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

    private function _fetchIdentities($limit, $offset, $sort, $order)
    {
        $ret = array();
        $db = $this->getXoopsDB();
        $sql = sprintf('SELECT * FROM %susers ORDER BY %s %s', $db->getResourcePrefix(), $sort, $order);
        if ($rs = $db->query($sql, $limit, $offset)) {
            while ($row = $rs->fetchAssoc()) {
                $ret[] = $this->_buildIdentity($row);
            }
        }

        return $ret;
    }

    public function userFetchIdentityByUsername($userName)
    {
        $db = $this->getXoopsDB();
        $sql = sprintf('SELECT * FROM %susers WHERE uname = %s', $db->getResourcePrefix(), $db->escapeString($userName));
        if (($rs = $db->query($sql, 1, 0)) &&
            $row = $rs->fetchAssoc()
        ) {
            return $this->_buildIdentity($row);
        }
        return false;
    }

    public function userFetchIdentityByEmail($email)
    {
        $db = $this->getXoopsDB();
        $sql = sprintf('SELECT * FROM %susers WHERE email = %s', $db->getResourcePrefix(), $db->escapeString($email));
        if (($rs = $db->query($sql, 1, 0)) &&
            $row = $rs->fetchAssoc()
        ) {
            return $this->_buildIdentity($row);
        }
        return false;
    }

    public function userCountIdentities()
    {
        $db = $this->getXoopsDB();
        $sql = sprintf('SELECT COUNT(*) FROM %susers', $db->getResourcePrefix());
        if ($rs = $db->query($sql)) {
            return $rs->fetchSingle();
        }
        return 0;
    }

    public function userGetIdentityPasswordById($userId)
    {
        $db = $this->getXoopsDB();
        $sql = sprintf('SELECT pass FROM %susers WHERE uid = %d', $db->getResourcePrefix(), $userId);
        if ($rs = $db->query($sql)) {
            return $rs->fetchSingle();
        }
        return false;
    }

    public function userGetRoleIdsById($userId)
    {
        if (!$this->_application->isType(Plugg::XOOPSCUBE_LEGACY)) return array();

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
        $params = $this->_application->isType(Plugg::XOOPSCUBE_LEGACY) ? array() : array(
            'xoopsUrl' => array(
                '#type' => 'url',
                '#title' => $this->_('XOOPS URL'),
                '#description' => $this->_('Enter the value of XOOPS_URL defined in the target XOOPSCube system.'),
                '#required' => true,
                '#size' => 80,
                '#default_value' => $this->getConfig('xoopsUrl'),
            ),
            'database' => array(
                '#title' => $this->_('Database settings'),
                'hostname' => array(
                    '#title' => $this->_('Database hostname'),
                    '#description' => $this->_('Enter the value of XOOPS_DB_HOST defined in the target XOOPSCube system.'),
                    '#required' => true,
                    '#size' => 50,
                    '#cacheable' => false,
                    '#default_value' => $this->getConfig('database', 'hostname'),
                ),
                'scheme' => array(
                    '#title' => $this->_('Database scheme'),
                    '#description' => $this->_('Enter the value of XOOPS_DB_SCHEME defined in the target XOOPSCube system.'),
                    '#required' => true,
                    '#size' => 50,
                    '#alnum' => true,
                    '#cacheable' => false,
                    '#default_value' => $this->getConfig('database', 'scheme'),
                ),
                'name' => array(
                    '#title' => $this->_('Database name'),
                    '#description' => $this->_('Enter the value of XOOPS_DB_NAME defined in the target XOOPSCube system.'),
                    '#required' => true,
                    '#size' => 50,
                    '#cacheable' => false,
                    '#default_value' => $this->getConfig('database', 'name'),
                ),
                'user' => array(
                    '#title' => $this->_('Database user name'),
                    '#description' => $this->_('Enter the value of XOOPS_DB_USER defined in the target XOOPSCube system.'),
                    '#required' => true,
                    '#size' => 50,
                    '#cacheable' => false,
                    '#default_value' => $this->getConfig('database', 'user'),
                ),
                'password' => array(
                    '#title' => $this->_('Database user password'),
                    '#description' => $this->_('Enter the value of XOOPS_DB_PASS defined in the target XOOPSCube system.'),
                    '#size' => 50,
                    '#cacheable' => false,
                    '#type' => 'password',
                    '#default_value' => $this->getConfig('database', 'password'),
                ),
                'tablePrefix' => array(
                    '#title' => $this->_('Database table prefix'),
                    '#description' => $this->_('Enter the value of XOOPS_DB_PREFIX defined in the target XOOPSCube system.'),
                    '#required' => true,
                    '#cacheable' => false,
                    '#default_value' => $this->getConfig('database', 'tablePrefix'),
                )
            )
        );

        $field_options = array(
            self::FIELD_VIEWABLE => $this->_('Display on profile page'),
            self::FIELD_EDITABLE => $this->_('Display on edit profile page'),
            self::FIELD_REGISTERABLE => $this->_('Display on registration page')
        );
        $field_options2 = array(
            self::FIELD_EDITABLE => $this->_('Display on edit profile page'),
            self::FIELD_REGISTERABLE => $this->_('Display on registration page')
        );

        return array_merge($params, array(
            'username' => array(
                '#tree' => true,
                '#weight' => 1,
                '#type' => 'fieldset',
                '#title' => $this->_('User name settings'),
                '#collapsible' => true,
                'minLength' => array(
                    '#title' => $this->_('User name minimum length'),
                    '#required' => true,
                    '#size' => 5,
                    '#default_value' => $this->getConfig('username', 'minLength'),
                ),
                'maxLength' => array(
                    '#title' => $this->_('User name maximum length'),
                    '#required' => true,
                    '#size' => 5,
                    '#default_value' => $this->getConfig('username', 'minLength'),
                ),
                'restriction' => array(
                    '#title' => $this->_('User name restriction'),
                    '#description' => $this->_('Select the restriction level of allowed characters in a user name'),
                    '#required' => true,
                    '#type' => 'radios',
                    '#options' => array(
                        'strict' => $this->_('Strict (Only alphabets, numbers, underscores, and dashes, RECOMMENDED)'),
                        'medium' => $this->_('Medium (Strict + some punctuation characters)'),
                        'light' => $this->_('Light (Medium + multi-byte characters)'),
                    ),
                    '#default_value' => $this->getConfig('username', 'restriction'),
                ),
                'usernamesNotAllowed' => array(
                    '#title' => $this->_('Restricted user names'),
                    '#description' => $this->_('Enter user names that are not allowed to be used, each per line. Regular expressions may be used.'),
                    '#type' => 'textmulti',
                    '#default_value' => $this->getConfig('username', 'usernamesNotAllowed'),
                ),
            ),
            'password' => array(
                '#tree' => true,
                '#weight' => 2,
                '#type' => 'fieldset',
                '#title' => $this->_('User password settings'),
                '#collapsible' => true,
                'minLength' => array(
                    '#title' => $this->_('Password minimum length'),
                    '#required' => true,
                    '#size' => 5,
                    '#default_value' => $this->getConfig('password', 'minLength'),
                )
            ),
            'email' => array(
                '#tree' => true,
                '#weight' => 3,
                '#type' => 'fieldset',
                '#title' => $this->_('User email settings'),
                '#collapsible' => true,
                'emailsNotAllowed' => array(
                    '#title' => $this->_('Restricted email addresses'),
                    '#description' => $this->_('Enter email addresses that are not allowed to be used, each per line. Regular expressions may be used.'),
                    '#type' => 'textmulti',
                    '#default_value' => $this->getConfig('email', 'emailsNotAllowed'),
                ),
            ),
            'fields' => array(
                '#weight' => 4,
                '#type' => 'fieldset',
                '#title' => $this->_('User field settings'),
                '#collapsible' => true,
                'name' => array(
                    '#title' => $this->_('"Full name" user field'),
                    '#type' => 'checkboxes',
                    '#options' => $field_options,
                    '#default_value' => $this->getConfig('fields', 'name'),
                ),
                'url' => array(
                    '#title' => $this->_('"URL" user field'),
                    '#type' => 'checkboxes',
                    '#options' => $field_options,
                    '#default_value' => $this->getConfig('fields', 'url'),
                ),
                'timezone' => array(
                    '#title' => $this->_('"Time zone" user field'),
                    '#type' => 'checkboxes',
                    '#options' => $field_options2,
                    '#dependency' => array('app' => Plugg::XOOPSCUBE_LEGACY),
                    '#default_value' => $this->getConfig('fields', 'timezone'),
                ),
                'imAccounts' => array(
                    '#title' => $this->_('"IM accounts" user field'),
                    '#type' => 'checkboxes',
                    '#options' => $field_options,
                    '#default_value' => $this->getConfig('fields', 'imAccounts'),
                ),
                'location' => array(
                    '#title' => $this->_('"Location" user field'),
                    '#type' => 'checkboxes',
                    '#options' => $field_options,
                    '#default_value' => $this->getConfig('fields', 'location'),
                ),
                'occupation' => array(
                    '#title' => $this->_('"Occupation" user field'),
                    '#type' => 'checkboxes',
                    '#options' => $field_options,
                    '#default_value' => $this->getConfig('fields', 'occupation'),
                ),
                'interests' => array(
                    '#title' => $this->_('"Interests" user field'),
                    '#type' => 'checkboxes',
                    '#options' => $field_options,
                    '#default_value' => $this->getConfig('fields', 'interests'),
                ),
                'sitePreferences' => array(
                    '#title' => $this->_('"Site preferences" user field'),
                    '#type' => 'checkboxes',
                    '#options' => $field_options2,
                    '#dependency' => array('app' => Plugg::XOOPSCUBE_LEGACY),
                    '#default_value' => $this->getConfig('fields', 'sitePreferences'),
                ),
                'extraInfo' => array(
                    '#title' => $this->_('"About me" user field'),
                    '#type' => 'checkboxes',
                    '#options' => $field_options,
                    '#default_value' => $this->getConfig('fields', 'extraInfo'),
                ),
                'enableStatFields' => array(
                    '#title' => $this->_('Enable XOOPS statistic fields.'),
                    '#type' => 'checkbox',
                    '#default_value' => $this->getConfig('fields', 'enableStatFields'),
                ),
            ),
            'avatar' => array(
                '#weight' => 5,
                '#type' => 'fieldset',
                '#title' => $this->_('User avatar settings'),
                '#tree' => true,
                '#collapsible' => true,
                'allowUpload' => array(
                    '#title' => $this->_('Allow avatar image upload'),
                    '#type' => 'checkbox',
                    '#default_value' => $this->getConfig('avatar', 'allowUpload'),
                ),
                'maxSizeKB' => array(
                    '#title' => $this->_('Avatar image max file size'),
                    '#description' => $this->_('Enter a numeric value in kilo bytes.'),
                    '#numeric' => true,
                    '#size' => 6,
                    '#required' => true,
                    '#default_value' => $this->getConfig('avatar', 'maxSizeKB'),
                ),
                'maxWidth' => array(
                    '#title' => $this->_('Avatar image max file width'),
                    '#description' => $this->_('Enter a numeric value in pixels, 0 for unlimited.'),
                    '#numeric' => true,
                    '#size' => 6,
                    '#required' => true,
                    '#default_value' => $this->getConfig('avatar', 'maxWidth'),
                ),
                'maxHeight' => array(
                    '#title' => $this->_('Avatar image max file height'),
                    '#description' => $this->_('Enter a numeric value in pixels, 0 for unlimited.'),
                    '#numeric' => true,
                    '#size' => 6,
                    '#required' => true,
                    '#default_value' => $this->getConfig('avatar', 'maxHeight'),
                )
            ),
        ));
    }

    private function _buildIdentity($rowData)
    {
        $identity = new Plugg_User_Identity($rowData['uid'], $rowData['uname']);
        $identity->name = $rowData['name'];
        $identity->email = $rowData['email'];
        $identity->url = $rowData['url'];
        $identity->created = $rowData['user_regdate'];
        if ($rowData['user_avatar'] !== 'blank.gif') {
            $identity->image = $identity->image_thumbnail = $identity->image_icon = $this->getXoopsUploadUrl() . '/' . $rowData['user_avatar'];
        } else {
            $identity->image = $identity->image_thumbnail = $identity->image_icon = $this->getXoopsUrl() . '/modules/user/images/no_avatar.gif';
        }
        
        return $identity;
    }

    private function _getIdentityById($id)
    {
        if ($identities = $this->userFetchIdentitiesByIds(array($id))) {
            return $identities[$id];
        }
        return false;
    }

    public function isUnameRegistered($uname)
    {
        $db = $this->getXoopsDB();
        $sql = sprintf('SELECT uid FROM %susers WHERE uname = %s', $db->getResourcePrefix(), $db->escapeString($uname));
        if (($rs = $db->query($sql, 1, 0)) && ($row = $rs->fetchRow())) {
            return $row[0];
        }
        return false;
    }

    public function isEmailRegistered($email)
    {
        $db = $this->getXoopsDB();
        $sql = sprintf('SELECT uid FROM %susers WHERE email = %s', $db->getResourcePrefix(), $db->escapeString($email));
        if (($rs = $db->query($sql, 1, 0)) && ($row = $rs->fetchRow())) {
            return $row[0];
        }
        return false;
    }

    public function getXoopsDB()
    {
        if (!isset($this->_xoopsDB)) {
            if ($this->_application->isType(Plugg::XOOPSCUBE_LEGACY)) {
                $params = array('tablePrefix' => XOOPS_DB_PREFIX . '_');
            } else {
                $this->loadConfig(); // Load params manually so that non-cacheable ones (db* params) become accessible
                $params = array(
                    'scheme' => $this->getConfig('database', 'scheme'),
                    'tablePrefix' => $this->getConfig('database', 'tablePrefix') . '_',
                    'charset' => SABAI_CHARSET,
                    'options' => array(
                        'host' => $this->getConfig('database', 'hostname'),
                        'dbname' => $this->getConfig('database', 'name'),
                        'user' => $this->getConfig('database', 'user'),
                        'pass' => $this->getConfig('database', 'password')
                    )
                );
            }
            $this->_xoopsDB = $this->_application->getLocator()->createService('DB', $params);
        }
        return $this->_xoopsDB;
    }

    public function getXoopsUrl()
    {
        if (!isset($this->_xoopsUrl)) {
            if ($this->_application->isType(Plugg::XOOPSCUBE_LEGACY)) {
                $this->_xoopsUrl = XOOPS_URL;
            } else {
                $this->_xoopsUrl = $this->getConfig('xoopsUrl');
            }
        }
        return $this->_xoopsUrl;
    }

    public function getXoopsUsersGroupId()
    {
        return $this->_application->isType(Plugg::XOOPSCUBE_LEGACY) ? XOOPS_GROUP_USERS : $this->getConfig('usersGroupId');
    }

    public function getXoopsUploadPath()
    {
        return $this->_application->isType(Plugg::XOOPSCUBE_LEGACY) ? XOOPS_UPLOAD_PATH : false;
    }
    
    public function getXoopsUploadUrl()
    {
        return $this->_application->isType(Plugg::XOOPSCUBE_LEGACY) ? XOOPS_UPLOAD_URL: $this->getConfig('xoopsUrl') . '/uploads';
    }

    public function onUserLoginSuccess($user)
    {
        if (!$this->_application->isType(Plugg::XOOPSCUBE_LEGACY)) return;

        $db = $this->getXoopsDB();
        $sql = sprintf('UPDATE %susers SET last_login = %d WHERE uid = %d', $db->getResourcePrefix(), time(), $user->id);
        $db->exec($sql);
    }
    
    public function getDefaultConfig()
    {
        return array(
            'xoopsUrl' => 'http://',
            'database' => array(
                'hostname' => 'localhost',
                'scheme' => 'mysql',
                'name' => 'xoopscube',
                'user' => 'root',
                'password' => '',
                'tablePrefix' => 'xoops',
            ),
            'username' => array(
                'minLength' => 3,
                'maxLength' => 10,
                'restriction' => 'strict',
                'usernamesNotAllowed' => array('webmaster', '^xoops', '^admin'),
            ),
            'password' => array(
                'minLength' => 8,
            ),
            'email' => array(
                'emailsNotAllowed' => array(),
            ),
            'fields' => array(
                'name' => array(1, 2),
                'url' => array(1, 2, 4),
                'timezone' => array(2, 4),
                'imAccounts' => array(1, 2),
                'location' => array(1, 2),
                'occupation' => array(1, 2),
                'interests' => array(1, 2),
                'sitePreferences' => array(2),
                'extraInfo' => array(1, 2),
            ),
            'avatar' => array(
                'allowUpload' => false,
                'maxSizeKB' => 100,
                'maxWidth' => 200,
                'maxHeight' => 200,
            ),
        );
    }
}
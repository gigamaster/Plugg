<?php
class Plugg_XOOPSCubeUser_RegisterFormBuilder extends Plugg_XOOPSCubeUser_FormBuilder
{
    public function buildForm(array &$settings)
    {
        // uname
        $settings['uname'] = array(
            '#title' => $this->_plugin->_('User name'),
            '#description' => sprintf(
                $this->_plugin->_('User name must be %d to %d characters long.'),
                $this->_plugin->getConfig('username', 'minLength'),
                $this->_plugin->getConfig('username', 'maxLength')
            ),
            '#description' => $this->_plugin->_('Please enter your desired user name.'),
            '#size' => 20,
            '#minlength' => $this->_plugin->getConfig('username', 'minLength'),
            '#maxlength' => $this->_plugin->getConfig('username', 'maxLength') > 255 ? 255 : $this->_plugin->getConfig('username', 'maxLength'),
            '#required' => true,
        );

        // email
        $settings['emails'] = array(
            '#type' => 'fieldset',
            '#title' => $this->_plugin->_('Email Address'),
            '#description' => $this->_plugin->_('Please enter a valid email address for yourself. We will send you an email shortly that you must confirm to complete the sign-up process.'),
        );
        $settings['emails']['email'] = array(
            '#type' => 'email',
            '#title' => $this->_plugin->_('Email'),
            '#size' => 50,
            '#maxlength' => 255,
            '#required' => true,
        );
        $settings['emails']['email_confirm'] = array(
            '#type' => 'email',
            '#title' => $this->_plugin->_('Confirm email'),
            '#description' => $this->_plugin->_('Enter again for confirmation.'),
            '#size' => 50,
            '#maxlength' => 255,
            '#required' => true,
        );

        // password
        $settings['passwords'] = array(
            '#type' => 'fieldset',
            '#title' => $this->_plugin->_('Password'),
            '#description' => $this->_plugin->_('Please enter a password for your user account. Note that passwords are case-sensitive.'),
        );
        $settings['passwords']['pass'] = array(
            '#type' => 'password',
            '#title' => $this->_plugin->_('Password'),
            '#description' => sprintf($this->_plugin->_('Password must be at least %d characters long.'), $this->_plugin->getConfig('password', 'minLength')),
            '#size' => 50,
            '#minlength' => $this->_plugin->getConfig('password', 'minLength'),
            '#required' => true,
        );
        $settings['passwords']['pass_confirm'] = array(
            '#type' => 'password',
            '#title' => $this->_plugin->_('Confirm Password'),
            '#description' => $this->_plugin->_('Enter again for confirmation.'),
            '#size' => 50,
            '#minlength' => $this->_plugin->getConfig('password', 'minLength'),
            '#required' => true,
        );

        $settings['#validate'][] = array($this, 'validateForm');

        parent::buildForm($settings);
    }

    public function validateForm(Plugg_Form_Form $form)
    {
        if ($this->_plugin->isUnameRegistered($form->values['uname'])) {
            $form->setError($this->_plugin->_('The username is already registered.'), 'uname');
        } else {
            $uname_level = $this->_plugin->getConfig('username', 'restriction');
            switch ($uname_level) {
                case 'light':
                    $uname_regex = "/[\000-\040]/";
                    break;
                case 'medium':
                    $uname_regex = '/[^a-zA-Z0-9_\-\<\>,\.\$%#@\!\\"' . "']/";
                    break;
                case 'strict':
                default:
                    $uname_regex = '/[^a-zA-Z0-9_\-]/';
            }
            if (preg_match_all($uname_regex, $form->values['uname'], $matches, PREG_PATTERN_ORDER)) {
                $form->setError($this->_plugin->_('Invalid character(s) found.'), 'uname');
            } else {
                foreach ($this->_plugin->getConfig('username', 'usernamesNotAllowed') as $uname_regex) {
                    if (!empty($uname_regex) && preg_match('/' . str_replace('/', '\/', $uname_regex) . '/', $form->values['uname'])) {
                        $form->setError(sprintf($this->_plugin->_('Entered user name may not be used (%s).'), $uname_regex), 'uname');
                    }
                }
            }
        }

        if ($form->values['pass'] != $form->values['pass_confirm']) {
            $form->setError($this->_plugin->_('The passwords do not match.'), 'passwords');
        }

        if ($form->values['email'] != $form->values['email_confirm']) {
            $form->setError($this->_plugin->_('The email addresses do not match.'), 'emails');
        } else {
            if ($this->_plugin->isEmailRegistered($form->values['email'])) {
                $form->setError($this->_('The email address is already registered.'), 'emails');
            } else {
                foreach ($this->_plugin->getConfig('email', 'emailsNotAllowed') as $email_regex) {
                    if (!empty($email_regex)
                        && preg_match('/' . str_replace('/', '\/', $email_regex) . '/i', $form->values['email'])
                    ) {
                        $form->setError(sprintf($this->_plugin->_('Entered email address may not be used (%s).'), $email_regex), 'emails');
                    }
                }
            }
        }
    }

    protected function _isFieldEnabled($value)
    {
        return in_array(Plugg_XOOPSCubeUser_Plugin::FIELD_REGISTERABLE, (array)$value);
    }
}
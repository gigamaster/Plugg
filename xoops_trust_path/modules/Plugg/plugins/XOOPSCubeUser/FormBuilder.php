<?php
abstract class Plugg_XOOPSCubeUser_FormBuilder
{
    protected $_plugin;

    public function __construct(Plugg_XOOPSCubeUser_Plugin $plugin)
    {
        $this->_plugin = $plugin;
    }

    public function buildForm(array &$settings, array $currentValues = array())
    {
        // name
        $this->_addNameField($settings, $currentValues);
        // url
        $this->_addUrlField($settings, $currentValues);
        // time zone
        $this->_addTimeZoneField($settings, $currentValues);
        // IM accounts
        $this->_addImAccountsField($settings, $currentValues);
        // location
        $this->_addLocationField($settings, $currentValues);
        // occupation
        $this->_addOccupationField($settings, $currentValues);
        // interests
        $this->_addInterestsField($settings, $currentValues);
        // site preferences
        $this->_addSitePreferencesField($settings, $currentValues);
        // other
        $this->_addExtraInfoField($settings, $currentValues);
        
        if (!empty($settings['basic'])) {
            $settings['basic']['#title'] = $this->_plugin->_('Basic information');
            $settings['basic']['#collapsible'] = true;
        }
    }

    protected function _addNameField(&$settings, $currentValues)
    {
        if (!$this->_isFieldEnabled($this->_plugin->getConfig('fields', 'name'))) return;

        $settings['basic']['name'] = array(
            '#title' => $this->_plugin->_('Full name'),
            '#description' => $this->_plugin->_('Enter your full name.'),
            '#size' => 50,
            '#maxlength' => 60,
            '#default_value' => @$currentValues['name'],
        );
    }

    protected function _addUrlField(&$settings, $currentValues)
    {
        if (!$this->_isFieldEnabled($this->_plugin->getConfig('fields', 'url'))) return;

        $settings['basic']['url'] = array(
            '#type' => 'url',
            '#title' => $this->_plugin->_('Website URL'),
            '#description' => $this->_plugin->_('Enter the URL of your website if any'),
            '#size' => 80,
            '#maxlength' => 100,
            '#default_value' => @$currentValues['url'],
        );
    }

    protected function _addTimeZoneField(&$settings, $currentValues)
    {
        if (!$this->_plugin->getApplication()->isType(Plugg::XOOPSCUBE_LEGACY) ||
            !$this->_isFieldEnabled($this->_plugin->getConfig('fields', 'timezone'))
        ) {
            return;
        }

        $xcube_root = XCube_Root::getSingleton();
        $xcube_root->mLanguageManager->loadModuleMessageCatalog('user');
        $xcube_root->mLanguageManager->loadPageTypeMessageCatalog('timezone');

        $options = array(
            '-12.0' => $this->_plugin->_(_TZ_GMTM12),
            '-11.0' => $this->_plugin->_(_TZ_GMTM11),
            '-10.0' => $this->_plugin->_(_TZ_GMTM10),
            '-9.0' => $this->_plugin->_(_TZ_GMTM9),
            '-8.0' => $this->_plugin->_(_TZ_GMTM8),
            '-7.0' => $this->_plugin->_(_TZ_GMTM7),
            '-6.0' => $this->_plugin->_(_TZ_GMTM6),
            '-5.0' => $this->_plugin->_(_TZ_GMTM5),
            '-4.0' => $this->_plugin->_(_TZ_GMTM4),
            '-3.5' => $this->_plugin->_(_TZ_GMTM35),
            '-3.0' => $this->_plugin->_(_TZ_GMTM3),
            '-2.0' => $this->_plugin->_(_TZ_GMTM2),
            '-1.0' => $this->_plugin->_(_TZ_GMTM1),
            '0.0' => $this->_plugin->_(_TZ_GMT0),
            '1.0' => $this->_plugin->_(_TZ_GMTP1),
            '2.0' => $this->_plugin->_(_TZ_GMTP2),
            '3.0' => $this->_plugin->_(_TZ_GMTP3),
            '3.5' => $this->_plugin->_(_TZ_GMTP35),
            '4.0' => $this->_plugin->_(_TZ_GMTP4),
            '4.5' => $this->_plugin->_(_TZ_GMTP45),
            '5.0' => $this->_plugin->_(_TZ_GMTP5),
            '5.5' => $this->_plugin->_(_TZ_GMTP55),
            '6.0' => $this->_plugin->_(_TZ_GMTP6),
            '7.0' => $this->_plugin->_(_TZ_GMTP7),
            '8.0' => $this->_plugin->_(_TZ_GMTP8),
            '9.0' => $this->_plugin->_(_TZ_GMTP9),
            '9.5' => $this->_plugin->_(_TZ_GMTP95),
            '10.0' => $this->_plugin->_(_TZ_GMTP10),
            '11.0' => $this->_plugin->_(_TZ_GMTP11),
            '12.0' => $this->_plugin->_(_TZ_GMTP12)
        );

        $default = $GLOBALS['xoopsConfig']['default_TZ'] % 1 ? (string)$GLOBALS['xoopsConfig']['default_TZ'] : intval($GLOBALS['xoopsConfig']['default_TZ']) . '.0';
        $settings['basic']['timezone_offset'] = array(
            '#type' => 'select',
            '#title' => $this->_plugin->_('Time zone'),
            '#description' => $this->_plugin->_('Select the appropriate time zone for your location.'),
            '#options' => $options,
            '#default_value' => isset($currentValues['timezone_offset']) ? $currentValues['timezone_offset'] : $default,
        );
    }

    protected function _addIMAccountsField(&$settings, $currentValues)
    {
        if (!$this->_isFieldEnabled($this->_plugin->getConfig('fields', 'imAccounts'))) return;

        $settings['im_accounts'] = array(
            '#type' => 'fieldset',
            '#title' => $this->_plugin->_('IM accounts'),
            '#description' => $this->_plugin->_('Enter your IM accounts below if any to let other users contact you easier.'),
            '#collapsible' => true,
        );
        $settings['im_accounts']['user_icq'] = array(
            '#type' => 'textfield',
            '#title' => $this->_plugin->_('ICQ'),
            '#size' => 50,
            '#maxlength' => 15,
            '#default_value' => isset($currentValues['user_icq']) ? $currentValues['user_icq'] : '',
        );
        $settings['im_accounts']['user_aim'] = array(
            '#type' => 'textfield',
            '#title' => $this->_plugin->_('AOL Instant Messenger'),
            '#size' => 50,
            '#maxlength' => 18,
            '#default_value' => isset($currentValues['user_icq']) ? $currentValues['user_aim'] : '',
        );
        $settings['im_accounts']['user_yim'] = array(
            '#type' => 'textfield',
            '#title' => $this->_plugin->_('Yahoo! Messenger'),
            '#size' => 50,
            '#maxlength' => 25,
            '#default_value' => isset($currentValues['user_icq']) ? $currentValues['user_yim'] : '',
        );
        $settings['im_accounts']['user_msnm'] = array(
            '#type' => 'textfield',
            '#title' => $this->_plugin->_('MSN Messenger'),
            '#size' => 50,
            '#maxlength' => 100,
            '#default_value' => isset($currentValues['user_icq']) ? $currentValues['user_msnm'] : '',
        );
    }

    protected function _addLocationField(&$settings, $currentValues)
    {
        if (!$this->_isFieldEnabled($this->_plugin->getConfig('fields', 'location'))) return;

        $settings['basic']['user_from'] = array(
            '#type' => 'textfield',
            '#title' => $this->_plugin->_('Location'),
            '#size' => 80,
            '#maxlength' => 100,
            '#default_value' => isset($currentValues['user_from']) ? $currentValues['user_from'] : '',
        );
    }

    protected function _addOccupationField(&$settings, $currentValues)
    {
        if (!$this->_isFieldEnabled($this->_plugin->getConfig('fields', 'occupation'))) return;

        $settings['basic']['user_occ'] = array(
            '#type' => 'textfield',
            '#title' => $this->_plugin->_('Occupation'),
            '#size' => 80,
            '#maxlength' => 100,
            '#default_value' => isset($currentValues['user_occ']) ? $currentValues['user_occ'] : '',
        );
    }

    protected function _addInterestsField(&$settings, $currentValues)
    {
        if (!$this->_isFieldEnabled($this->_plugin->getConfig('fields', 'interests'))) return;

        $settings['basic']['user_intrest'] = array(
            '#type' => 'textfield',
            '#title' => $this->_plugin->_('Interests'),
            '#description' => $this->_plugin->_('Enter things you are interested in.'),
            '#size' => 80,
            '#maxlength' => 150,
            '#default_value' => isset($currentValues['user_intrest']) ? $currentValues['user_intrest'] : '',
        );
    }

    protected function _addSitePreferencesField(&$settings, $currentValues)
    {
        if (!$this->_plugin->getApplication()->isType(Plugg::XOOPSCUBE_LEGACY) ||
            !$this->_isFieldEnabled($this->_plugin->getConfig('fields', 'sitePreferences'))
        ) {
            return;
        }

        $xcube_root = XCube_Root::getSingleton();
        $xcube_root->mLanguageManager->loadModuleMessageCatalog('user');
        $xcube_root->mLanguageManager->loadPageTypeMessageCatalog('notification');
        
        $umodes = array('nest' => h(_NESTED), 'flat' => h(_FLAT), 'thread' => h(_THREADED));
        $uorders = array(0 => h(_OLDESTFIRST), 1 => h(_NEWESTFIRST));
        $notify_methods = array(0 => h(_NOT_METHOD_DISABLE), 1 => h(_NOT_METHOD_PM), 2 => h(_NOT_METHOD_EMAIL));
        $notify_modes = array(0 => h(_NOT_MODE_SENDALWAYS), 1 => h(_NOT_MODE_SENDONCE), 2 => h(_NOT_MODE_SENDONCEPERLOGIN));

        $settings['site_options'] = array(
            '#title' => $this->_plugin->_('Site preferences'),
            '#description' => $this->_plugin->_('Select some options to make this website more useful to you. Note that not all contents support these features.'),
            '#collapsible' => true,
        );
        $settings['site_options']['umode'] = array(
            '#type' => 'select',
            '#title' => _MD_USER_LANG_UMODE,
            '#options' => $umodes,
            '#default_value' => @$currentValues['umode'],
        );
        $settings['site_options']['uorder'] = array(
            '#type' => 'select',
            '#title' => _MD_USER_LANG_UORDER,
            '#options' => $uorders,
            '#default_value' => @$currentValues['uorder'],
        );
        $settings['site_options']['notify_method'] = array(
            '#type' => 'select',
            '#title' => _MD_USER_LANG_NOTIFY_METHOD,
            '#description' => $this->_plugin->_('Select how you would like to receive notification messeges.'),
            '#options' => $notify_methods,
            '#default_value' => @$currentValues['notify_method'],
        );
        $settings['site_options']['notify_mode'] = array(
            '#type' => 'select',
            '#title' => _MD_USER_LANG_NOTIFY_MODE,
            '#options' => $notify_modes,
            '#default_value' => @$currentValues['notify_mode'],
        );
        $settings['site_options']['user_sig'] = array(
            '#type' => 'textarea',
            '#title' => _MD_USER_LANG_USER_SIG,
            '#description' => $this->_plugin->_('Enter your signature that may be attached to the end of your posted content.'),
            '#rows' => 10,
            '#cols' => 60,
            '#default_value' => @$currentValues['user_sig'],
        );
        $settings['site_options']['attachsig'] = array(
            '#type' => 'checkbox',
            '#title' => _MD_USER_LANG_ATTACHSIG,
            '#default_value' => !empty($currentValues['attachsig']),
        );
    }

    protected function _addExtraInfoField(&$settings, $currentValues)
    {
        if (!$this->_isFieldEnabled($this->_plugin->getConfig('fields', 'extraInfo'))) return;

        $settings['basic']['bio'] = array(
            '#type' => 'textarea',
            '#title' => $this->_plugin->_('About me'),
            '#description' => $this->_plugin->_('Enter any extra information you would like other users to see on your profile page.'),
            '#rows' => 10,
            '#cols' => 60,
            '#default_value' => isset($currentValues['bio']) ? $currentValues['bio'] : '',
        );
    }

    abstract protected function _isFieldEnabled($value);
}
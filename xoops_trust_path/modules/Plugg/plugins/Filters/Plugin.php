<?php
class Plugg_Filters_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Admin, Plugg_Form_Field, Plugg_User_Permissionable, Plugg_User_AccountSetting
{
    private $_filters;

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/system/settings/filters' => array(
                'controller' => 'System_Settings',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
            ),
            '/system/settings/filters/submit' => array(
                'controller' => 'System_Settings_Submit',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/system/settings/filters/:filter_id' => array(
                'format' => array(':filter_id' => '\d+'),
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/system/settings/filters/:filter_id/configure' => array(
                'controller' => 'System_Settings_Filter_Configure',
                'access_callback' => true,
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/system/settings/filters/:filter_id':
                return ($this->_application->filter = $this->getRequestedEntity($request, 'Filter', 'filter_id'))
                    && $this->_application->getPlugin($this->_application->filter->plugin);

            // Make sure the filter has any configuration options
            case '/system/settings/filters/:filter_id/configure':
                return $this->_application->getPlugin($this->_application->filter->plugin)
                    ->filtersFilterGetSettings($this->_application->filter->name);
        }
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/system/settings/filters/:filter_id':
                return $this->_application->getPlugin($this->_application->filter->plugin)
                    ->filtersFilterGetNicename($this->_application->filter->name);
        }
    }

    /* End implementation of Plugg_System_Routable_Admin */
    
    /* Start implementation of Plugg_User_AccountSetting */
    
    public function userAccountSettingGetNames()
    {
        return array('default_text_format');
    }
    
    public function userAccountSettingGetSettings($settingName, Plugg_User_Identity $identity)
    {
        switch ($settingName) {
            case 'default_text_format':
                list($options, $default_filter_id, $tips) = $this->_createFilterSelectOptions($this->getFilters($identity), $identity);
                return array(
                    '#type' => 'radios',
                    '#title' => $this->_('Default text format'),
                    '#description' => $this->_('Select the default text format to be applied to your text content.'),
                    '#default_value' => $default_filter_id,
                    '#options' => $options,
                );
        }
    }

    /* End implementation of Plugg_User_AccountSetting */
    
    /* Start implementation of Plugg_User_Permissionable */

    public function userPermissionableGetPermissions()
    {
        $perms = array();
        foreach ($this->_getFilters() as $filter) {
            if ($filter->default) continue;
            if ($plugin = $this->_application->getPlugin($filter->plugin)) {
                $perms['filter use filter ' . $filter->id] = sprintf($this->_('Use the "%s (%s %s)" filter'), $filter->title, $filter->plugin, $filter->name);
            }
        }
        
        return $perms;
    }

    public function userPermissionableGetDefaultPermissions()
    {
        return array();
    }
    
    /* End implementation of Plugg_User_Permissionable */

    /**
     * Called when a plugin that implements the Plugg_Filters_Filter interface is installed
     */
    public function onFiltersFilterInstalled($pluginEntity, $plugin)
    {
        if ($filters = $plugin->filtersFilterGetNames()) {
            $this->_createPluginFilters($pluginEntity->name, $filters);
        }
    }

    /**
     * Called when a plugin that implements the Plugg_Filters_Filter interface is uninstalled
     */
    public function onFiltersFilterUninstalled($pluginEntity, $plugin)
    {
        $this->_deletePluginFilters($pluginEntity->name);
    }

    /**
     * Called when a plugin that implements the Plugg_Filters_Filter interface is upgraded
     */
    public function onFiltersFilterUpgraded($pluginEntity, $plugin)
    {
        // Update filters
        if (!$filters = $plugin->filtersFilterGetNames()) {
            $this->_deletePluginFilters($pluginEntity->name);
        } else {
            $model = $this->getModel();
            $filters_already_installed = array();
            foreach ($model->Filter->criteria()->plugin_is($plugin_name)->fetch() as $current_filter) {
                if (in_array($current_filter->name, $filters)) {
                    $filters_already_installed[] = $current_filter->name;
                } else {
                    $current_filter->markRemoved();
                }
            }
            $this->_createPluginFilters($plugin_name, array_diff($filters, $filters_already_installed));
        }
    }

    private function _createPluginFilters($pluginName, $filters)
    {
        $model = $this->getModel();
        foreach ($filters as $filter_name => $filter_title) {
            if (empty($filter_name)) continue;
            $filter = $model->create('Filter');
            $filter->name = $filter_name;
            $filter->title = $filter_title;
            $filter->plugin = $pluginName;
            $filter->active = 1;
            $filter->markNew();
        }

        return $model->commit();
    }

    private function _deletePluginFilters($pluginName)
    {
        $model = $this->getModel();
        foreach ($model->Filter->criteria()->plugin_is($pluginName)->fetch() as $e) {
            $e->markRemoved();
        }

        return $model->commit();
    }

    public function filterQuotedText($text, $nl2br = false, $startTag = '<blockquote>', $endTag = '</blockquote>')
    {
        $bool_str = $nl2br ? 'true' : 'false';
        $text = preg_replace_callback(
            '/\n((\>).*\n)(?!(\>))/Us',
            create_function(
                '$matches',
                "return Plugg_Filters_Plugin::filterQuotedTextStatic(\$matches[1], $bool_str, '$startTag', '$endTag');"
            ),
            "\n" . $text . "\n"
        );

        // Remove added \n
        return trim($text, "\n");
    }

    public static function filterQuotedTextStatic($text, $nl2br = false, $startTag = '<blockquote>', $endTag = '</blockquote>')
    {
        $ret = '';

        preg_match_all(
            '/^(\>+) (.*\n)/Ums',
            $text,
            $matches,
            PREG_SET_ORDER
        );

        $current_level = 0;

        // loop through each list-item element.
        foreach ($matches as $key => $val) {

            // $val[0] is the full matched list-item line
            // $val[1] is the number of initial '>' chars (indent level)
            // $val[2] is the quote text

            // we number levels starting at 1, not zero
            $level = strlen($val[1]);

            // add a level to the list?
            if ($level > $current_level) {
                while ($level > $current_level) {
                    // the current indent level is greater than the number
                    // of stack elements, so we must be starting a new
                    // level.  push the new level onto the stack with a
                    // dummy value (boolean true)...
                    ++$current_level;

                    // ...and add a start token to the return.
                    $ret .= $startTag;
                }


            // remove a level?
            } elseif ($current_level > $level) {
                while ($current_level > $level) {
                    // as long as the stack count is greater than the
                    // current indent level, we need to end list types.
                    // continue adding end-list tokens until the stack count
                    // and the indent level are the same.
                    --$current_level;

                    $ret .= $endTag;
                }

            } else {
                if ($nl2br) {
                    $ret .= '<br />';
                }
            }

            // add the line text.
            $ret .= $val[2];
        }

        // the last char of the matched pattern must be \n but we don't
        // want this to be inside the tokens
        $ret = substr($ret, 0, -1);

        // the last line may have been indented.  go through the stack
        // and create end-tokens until the stack is empty.

        while ($current_level > 0) {
            $ret .= $endTag;
            --$current_level;
        }

        // put back the trailing \n
        $ret .= "\n";

        // we're done!  send back the replacement text.
        return $ret;
    }

    public function getFilters($user)
    {
        $ret = array();

        if ($user instanceof Sabai_User) {
            if ($user->isSuperUser()) {
                $is_super_user = true;
            } else {
                $user_permissions = $user->getPermissions();
            }
        } elseif ($user instanceof Sabai_User_Identity) {
            $user_permissions = $this->_application->User_IdentityPermissions($user);
            // If true is returned, the owner of identity is a super user
            if ($user_permissions === true) $is_super_user = true;
        } else {
            $user_permissions = array();
        }

        foreach  ($this->_getFilters() as $filter) {
            if (empty($is_super_user)) {
                if (!$filter->default && !in_array('filter use filter ' . $filter->id, $user_permissions)) {
                    continue;
                }
            }

            if (!$filter_plugin = $this->_application->getPlugin($filter->plugin)) continue;

            $ret[$filter->id] = array(
                'name' => $filter->name,
                'title' => $filter->title,
                'tips' => $filter_plugin->filtersFilterGetTips($filter->name, false),
                'plugin' => $filter_plugin,
            );
        }
        return $ret;
    }

    private function _getFilters()
    {
        if (!isset($this->_filters)) {
            $this->_filters = $this->getModel()->Filter
                ->criteria()
                ->active_is(1)
                ->fetch(0, 0, 'order', 'ASC');
        }
        return $this->_filters;
    }

    /* Start implementation of Plugg_Form_Field */

    public function formFieldGetFormElementTypes()
    {
        return array('filters_textarea' => Plugg_Form_Plugin::FORM_FIELD_NORMAL);
    }
    
    public function formFieldGetTitle($type)
    {
        switch ($type) {
            case 'filters_textarea':
                return $this->_('Filterable textarea');
        }
    }
    
    public function formFieldGetSummary($type)
    {
        switch ($type) {
            case 'filters_textarea':
                return $this->_('Displays a textarea field and its input format selection field. Input format selection is generated from available filter plugins installed on the system.');
        }
    }

    public function formFieldGetFormElement($type, $name, array &$data, Plugg_Form_Form $form)
    {
        switch ($type) {
            case 'filters_textarea':
                // Fetch user identity. If no user id is specified, use the current user id
                if (empty($data['#user_id'])) $data['#user_id'] = $this->_application->getUser()->id;
                $identity = $this->_application->getPlugin('User')->getIdentity($data['#user_id']);

                // Create filter options avaialble for the user
                list($options, $default_filter_id, $tips) = $this->_createFilterSelectOptions($this->getFilters($identity), $identity);
                if (!empty($data['#label'][0])) {
                    $options_label = sprintf($this->_('%s - Input format'), $data['#label'][0]);
                } else {
                    $options_label = $this->_('Input format');
                }

                // Define element settings
                $textarea_settings = $data;
                $textarea_settings['#type'] = 'textarea';
                $textarea_settings['#default_value'] = @$data['#default_value']['text'];
                unset($textarea_settings['#label'], $textarea_settings['#title'], $textarea_settings['#description']);
                $ele_data = array(
                    '#label' => $data['#label'],
                    '#tree' => true,
                    '#children' => array(
                        0 => array(
                            'text' => $textarea_settings + $form->defaultElementSettings(),
                            'filter_id' => array(
                                '#title' => $options_label,
                                '#type' => 'radios',
                                '#options' => $options,
                                '#default_value' => !empty($data['#default_value']['filter_id']) ? $data['#default_value']['filter_id'] : $default_filter_id,
                                '#options_description' => $tips,
                                '#collapsible' => true,
                                '#collapsed' => true,
                            ) + $form->defaultElementSettings(),
                            'filtered_text' => array(
                                '#type' => 'hidden',
                            ) + $form->defaultElementSettings()
                        )
                    )
                ) + $form->defaultElementSettings();

                return $form->createElement('fieldset', $name, $ele_data);
        }
    }

    public function formFieldOnSubmitForm($type, $name, &$value, array &$data, Plugg_Form_Form $form)
    {
        switch ($type) {
            case 'filters_textarea':
                // Validate required/min_length/max_length settings
                if (!$this->_application->getPlugin('Form')->validateFormElementText($form, $name . '[text]', $value['text'], $data)) {
                    return false;
                }
                
                if (strlen($value['text']) === 0) {
                    // No text to filter
                    $value['filtered_text'] = '';
                    $value['filter_id'] = null;
                    
                    return;
                }

                // Make sure a filter has been selected
                if (empty($value['filter_id'])) {
                    $form->setError($this->_('Invalid filter.'), $name . '[filter_id]');

                    return false;
                }

                // Get the selected filter ID
                $filter_id = $value['filter_id'][0];

                // Get user identity for whom available filters will be fetched. If no user ID specified, use the current user ID.
                if (empty($data['#user_id'])) $data['#user_id'] = $this->_application->getUser()->id;
                $identity = $this->_application->getPlugin('User')->getIdentity($data['#user_id']);

                // Get filters available for the user
                $filters = $this->getFilters($identity);

                // Make sure the selected filter exists
                if (empty($filters) || (!$filter = @$filters[$filter_id])) {
                    $form->setError($this->_('Invalid filter.'), $name . '[filter_id]');

                    return false;
                }

                // Do filter
                $value['filtered_text'] = $value['text'];
                if (strlen($value['filtered_text'])) {
                    $value['filtered_text'] = $filter['plugin']->filtersFilterText($filter['name'], $value['filtered_text']);
                }
                
                $value['filter_id'] = $filter_id;
        }
    }
    
    public function formFieldOnCleanupForm($type, $name, array $data, Plugg_Form_Form $form){}

    public function formFieldGetSettings($type, array $currentValues)
    {
        switch ($type) {
            case 'filters_textarea':
                return $this->_application->getPlugin('Form')->formFieldGetSettings('textarea', $currentValues);
        }
    }
    
    public function formFieldRenderHtml($type, $value, array $data, array $allValues = array())
    {
        switch ($type) {
            case 'filters_textarea':
                return $value['filtered_text'];
        }
    }

    /* End implementation of Plugg_Form_Field */

    private function _createFilterSelectOptions($filters, $identity)
    {
        $options = $tips = array();
        foreach ($filters as $filter_id => $filter) {
            $tips[$filter_id] = empty($filter['tips']) ? '' : '<ul><li>' . implode('</li><li>', $filter['tips']) . '</li></ul>';
            $options[$filter_id] = h($filter['title']);
        }

        // Get user selected default filter if any
        if (!$identity->isAnonymous()) {
            if (!$identity->isDataLoaded()) {
                // Load extra data associated with this user identity
                $this->_application->getPlugin('User')->getIdentityFetcher()->loadIdentityWithData($identity);
            }
            if ($identity->hasData('filters_default_text_format')) {
                $default_filter_id = $identity->getData('filters_default_text_format');
            }
        }

        return array($options, @$default_filter_id, $tips);
    }
}
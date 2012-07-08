<?php
class Plugg_XOOPSCube_Plugin extends Plugg_Plugin implements Plugg_Search_Searchable, Plugg_User_Widget, Plugg_Mail_Mailer, Plugg_User_Menu
{
    private $_db;
    private $_searches;

    public function onMainControllerEnter($request, $response)
    {
        // a css file provided by the current theme will override other css files
        if (file_exists($css_file = XOOPS_ROOT_PATH . '/themes/' . $GLOBALS['xoopsConfig']['theme_set'] . '/plugg/style.css')) {
            $response->addCssFile(str_replace(XOOPS_ROOT_PATH, XOOPS_URL, $css_file), 'screen', null, 99);
        }
    }
    
    public function onSystemAdminPluginUninstalled($pluginEntity)
    {
        $this->_deletePluginBlocks($pluginEntity->name);
    }

    public function onWidgetsWidgetInstalled($pluginEntity, $plugin)
    {
        if ($widgets = $plugin->widgetsGetWidgetNames()) {
            $this->_createPluginBlocks($widgets, $plugin);
        }
    }

    public function onWidgetsWidgetUpgraded($pluginEntity, $plugin)
    {
        $block_options = $this->_deletePluginBlocks($pluginEntity->name);
        $this->_createPluginBlocks($plugin->widgetsGetWidgetNames(), $plugin, $block_options);
    }

    private function _createPluginBlocks($widgets, $plugin, $blockOptions = array())
    {
        // Save widgets as XOOPS blocks
        $db = $this->_getDB();
        $module = $this->_getModule();
        $module_id = $module->get('mid');
        $module_dirname = $module->get('dirname');
        $block_handler = xoops_gethandler('block');
        $func_num = $this->_getLastBlockFuncNumByModule($block_handler, $module_id);
        $blocks = array();
        $block = $block_handler->create();
        $block->set('mid', $module_id);
        $block->set('block_type', 'M');
        $block->set('c_type', 1);
        $block->set('dirname', $module_dirname);
        $block->set('func_file', 'blocks.php');
        $block->set('show_func', 'b_plugg_widget');
        $block->set('edit_func', 'b_plugg_widget_edit');
        $block->set('template', '');
        $block->set('last_modified', time());
        foreach ($widgets as $widget_name => $widget_type) {
            if (!empty($blockOptions[$widget_name])) {
                // First 3 options must not be modified
                $block_options = array_merge(array($module_dirname, $plugin->name, $widget_name), array_slice($blockOptions[$widget_name], 3));
            } else {
                $block_options = array($module_dirname, $plugin->name, $widget_name, 1);
                if ($widget_settings = $plugin->widgetsGetWidgetSettings($widget_name)) {
                    foreach ($widget_settings as $widget_setting) {
                        if (!empty($widget_setting['#default_value'])) {
                            $block_options[] = is_array($widget_setting['#default_value'])
                                ? implode(' ', $widget_setting['#default_value'])
                                : $widget_setting['#default_value'];
                        }
                    }
                }
            }
            $block->set('func_num', ++$func_num);
            $block->set('options', implode('|', $block_options));
            $block->set('name', $widget_name . ' - ' . $plugin->name);
            $block->set('title', $plugin->widgetsGetWidgetTitle($widget_name) . ' - ' . $plugin->nicename);
            $block->set('side', 0);
            $block->set('weight', 0);
            $block->set('visible', 1);
            $block->set('isactive', 1);
            $block->set('bcachetime', 1);
            if ($block_handler->insert($block)) {
                $block_id = $block->get('bid');
                $blocks[$block_id] = $widget_name;

                // Display block on plugg module page by default
                $sql = sprintf(
                    'INSERT INTO %sblock_module_link (block_id, module_id) VALUES (%d, %d)',
                    $db->getResourcePrefix(), $block_id, $module_id
                );
                $db->exec($sql);
            }
        }

        if (empty($blocks)) return;

        // Insert block-group permissions
        $perm_handler = xoops_gethandler('groupperm');
        $perm = $perm_handler->create();
        $perm->set('gperm_name', 'block_read');
        $perm->set('gperm_modid', 1); // 1 for block permissions
        foreach (array_keys($blocks) as $block_id) {
            $perm->set('gperm_itemid', $block_id);
            foreach (array(XOOPS_GROUP_ADMIN, XOOPS_GROUP_USERS, XOOPS_GROUP_ANONYMOUS) as $group_id) {
                $perm->set('gperm_groupid', $group_id);
                $perm->setNew();
                $perm_handler->insert($perm);
            }
        }

        // Save block-to-widget associations
        $model = $this->getModel();
        foreach ($blocks as $block_id => $widget_name) {
            $block = $model->create('Block');
            $block->block_id = $block_id;
            $block->widget = $widget_name;
            $block->plugin = $plugin->name;
            $block->markNew();
        }
        $model->commit();
    }

     private function _deletePluginBlocks($pluginName)
     {
        // Get block-to-widget associations
        $model = $this->getModel();
        $widgets = $block_options = array();
        foreach ($model->Block->criteria()->plugin_is($pluginName)->fetch() as $block) {
            $widgets[$block->block_id] = $block->widget;
            $block->markRemoved();
        }
        $model->commit();

        if (!empty($widgets)) {
            // Remove blocks
            $block_handler = xoops_gethandler('block');
            $criteria = new Criteria('bid', '(' . implode(',', array_keys($widgets)) . ')', 'IN');
            $blocks = $block_handler->getObjectsDirectly($criteria);
            foreach (array_keys($blocks) as $i) {
                $widget_name = $widgets[$blocks[$i]->get('bid')];
                $block_options[$widget_name] = explode('|', $blocks[$i]->get('options'));
                $block_handler->delete($blocks[$i]);
            }

            // Remove group permissions
            $criterion = new CriteriaCompo();
            $criterion->add(new Criteria('gperm_name', 'block_read'));
            $criterion->add(new Criteria('gperm_itemid', '(' . implode(',', array_keys($widgets)) . ')', 'IN'));
            $criterion->add(new Criteria('gperm_modid', 1));
            xoops_gethandler('groupperm')->deleteAll($criterion);
        }

        return $block_options;
    }

    private function _getLastBlockFuncNumByModule($blockHandler, $moduleId)
    {
        $criteria = new Criteria('mid', $moduleId);
        // Can't set order using getObjectsDirectly()
        //$criteria->setLimit(1);
        //$criteria->setSort('func_num', 'DESC');
        $blocks = $blockHandler->getObjectsDirectly($criteria);
        $func_num = array(0);
        foreach (array_keys($blocks) as $i) {
            $func_num[] = $blocks[$i]->get('func_num');
        }
        sort($func_num, SORT_NUMERIC);

        return array_pop($func_num);
    }

    private function _getModule()
    {
        // Application ID is a module directory name in Plugg for XOOPSCube
        $module_dirname = $this->_application->getId();

        return xoops_gethandler('module')->getByDirname($module_dirname);
    }

    private function _getDB()
    {
        if (!isset($this->_db)) {
            $params = array('tablePrefix' => XOOPS_DB_PREFIX . '_');
            $this->_db = $this->_application->getLocator()->createService('DB', $params);
        }

        return $this->_db;
    }
    
    /* Start implmentation of Plugg_User_Widget */
    
    public function userWidgetGetNames()
    {
        return array(
            'default' => Plugg_User_Plugin::WIDGET_TYPE_PUBLIC | Plugg_User_Plugin::WIDGET_TYPE_CACHEABLE
        );
    }

    public function userWidgetGetTitle($widgetName)
    {
        switch ($widgetName) {
            case 'default':
                return $this->_('XOOPS Cube search results');
        }
    }

    public function userWidgetGetSummary($widgetName)
    {
        switch ($widgetName) {
            case 'default':
                return $this->_('Shows module contents submitted by the user that are not part of the Plugg module.');
        }
    }

    public function userWidgetGetSettings($widgetName, array $currentValues = array())
    {
        switch ($widgetName) {
            case 'default':
                return array(
                    'limit' => array(
                        '#type' => 'radios',
                        '#title' => $this->_('Number of search results to display for each module content'),
                        '#options' => array(1 => 1, 3 => 3, 5 => 5, 7 => 7, 10 => 10, 15 => 15, 20 => 20),
                        '#delimiter' => '&nbsp;',
                        '#default_value' => isset($currentValues['limit']) ? $currentValues['limit'] : 5,
                    ),
                );
        }
    }

    public function userWidgetGetContent($widgetName, $widgetSettings, Sabai_User_Identity $identity, $isOwner, $isAdmin)
    {
        switch ($widgetName) {
            case 'default':
                return $this->_renderDefaultUserWidget($widgetSettings, $identity);
        }
    }

    private function _renderDefaultUserWidget($widgetSettings, $identity)
    {
        $vars = array(
            'module_results' => array(),
            'identity' => $identity,
            'search_url' => XOOPS_URL . '/search.php'
        );
        $root = XCube_Root::getSingleton();
        if ($service = $root->mServiceManager->getService('LegacySearch')) {
            $client = $root->mServiceManager->createClient($service);
            $limit = $widgetSettings['limit'];
            foreach ($client->call('getActiveModules', array()) as $module) {
                $params = array(
                    'mid' => $module['mid'],
                    'uid' => $identity->id,
                    'maxhit' => $limit,
                    'start' => 0
                );
                if ($results = $client->call('searchItemsOfUser', $params)) {
                    $vars['module_results'][$module['mid']] = array(
                        'name' => $module['name'],
                        'results' => $results,
                        'has_more' => count($results) >= $limit
                    );
                }
            }
        }
        
        return array(
            'content' => $this->_application->RenderTemplate('xoopscube_user_widget_default', $vars, $this->_name)
        );
    }
    
    /* End implmentation of Plugg_User_Widget */

    public function onXOOPSCubeInstalled($pluginEntity)
    {
        $modules = xoops_gethandler('module')->getObjects(new Criteria('isactive', 1));

        foreach (array_keys($modules) as $i) {
            if ($modules[$i]->getVar('dirname') != 'Plugg'
                && ($module_plugg = $this->_isModulePluggable($modules[$i]))
            ) {
                if ($module_plugg instanceof Plugg_XOOPSCube_SearchableModule) {
                    $this->_insertOrUpdateModuleSearch($module_plugg);
                }
            }
        }
    }

    public function onXOOPSCubeModuleInstallSuccess($module)
    {
        if (!$module_plugg = $this->_isModulePluggable($module)) return;

        if ($module_plugg instanceof Plugg_XOOPSCube_SearchableModule) {
            $this->_insertOrUpdateModuleSearch($module_plugg);
        }
    }

    public function onXOOPSCubeModuleUninstallSuccess($module)
    {
        $this->_deleteModuleSearch($module);
    }

    public function onXOOPSCubeModuleUpdateSuccess($module)
    {
        if (!$module_plugg = $this->_isModulePluggable($module)) {
            $this->_deleteModuleSearch($module);
            return;
        }

        if ($module_plugg instanceof Plugg_XOOPSCube_SearchableModule) {
            $this->_insertOrUpdateModuleSearch($module_plugg);
        }
    }

    private function _insertOrUpdateModuleSearch(Plugg_XOOPSCube_Module $module)
    {
        $search_name = $module->getModuleVar('dirname');
        if (!$search_title = $module->searchGetTitle()) {
            $search_title = $module->getModuleVar('name');
        }

        $model = $this->getModel();
        $searches = $model->Search->criteria()->module_is($search_name)->fetch();
        if ($searches->count() > 0) {
            $search = $searches->getFirst();
        } else {
            $search = $model->create('Search');
            $search->module = $search_name;
            $search->markNew();
        }
        $search->name = $search_title;
        
        return $search->commit();
    }

    private function _deleteModuleSearch(XoopsModule $module)
    {
        $search_name = $module->getVar('dirname');
        $model = $this->getModel();
        $criteria = $model->createCriteria('Search')->module_is($search_name);
        if (false !== $model->getGateway('Search')->deleteByCriteria($criteria->module_is($search_name))) {
            if ($search_plugin = $this->_application->getPlugin('Search')) {
                return $search_plugin->deleteSearchable($this->name, $search_name);
            }
        }
        return false;
    }

    private function _isModulePluggable($module)
    {
        if (!is_object($module)
            && (!$module = xoops_gethandler('module')->getByDirname($module))
        ) {
            return false;        
        }
        
        $module_dirname = $module->getVar('dirname');
        $file_paths = array(
            sprintf('%s/modules/%s/Plugg.php', XOOPS_ROOT_PATH, $module_dirname),
            sprintf('%s/Module/%s.php', dirname(__FILE__), $module_dirname)
        );
        foreach ($file_paths as $file_path) {
            if (file_exists($file_path)) {
                require_once $file_path;
                $plugg_class = sprintf('Plugg_XOOPSCube_Module_%s', $module_dirname);
                if (class_exists($plugg_class, false)) {
                    return new $plugg_class($module);
                }
            }
        }

        return false;
    }
    
    private function _getPluggableModules()
    {
        $modules = $this->_getSearches();
        foreach (new DirectoryIterator(dirname(__FILE__) . '/Module') as $fileinfo) {
            if ($fileinfo->isFile()
                && strrpos($fileinfo->getFilename(), '.php', -4)
                && ($basename = $fileinfo->getBasename('.php'))
                && !isset($modules[$basename])
            ) {
                $modules[$basename] = false;
            }
        }
        
        return $modules;
    }

    private function _getSearches()
    {
        if (!isset($this->_searches)) {
            $this->_searches = array();
            foreach ($this->getModel()->Search->fetch() as $search) {
                $this->_searches[$search->module] = $search;
            }
        }

        return $this->_searches;
    }
    
    /* Start implementation of Plugg_Search_Searchable */

    public function searchGetNames()
    {
        $ret = array();
        foreach (array_keys($this->_getSearches()) as $module_name) {
            $ret[] = $module_name;
        }

        return $ret;
    }

    public function searchGetNicename($searchName)
    {
        $searches = $this->_getSearches();

        return isset($searches[$searchName]) ? $searches[$searchName]->name : '';
    }

    public function searchGetContentUrl($searchName, $contentId)
    {
        if (($module = $this->_isModulePluggable($searchName))
            && $module instanceof Plugg_XOOPSCube_SearchableModule
        ) {
            return $module->searchGetContentUrl($contentId);
        }
    }

    public function searchFetchContents($searchName, $limit, $offset)
    {
        $contents = array();
        if (($module = $this->_isModulePluggable($searchName))
            && $module instanceof Plugg_XOOPSCube_SearchableModule
        ) {
            $contents = $module->searchFetchContents($limit, $offset);
        }

        return new ArrayObject($contents);
    }

    public function searchCountContents($searchName)
    {
        if (($module = $this->_isModulePluggable($searchName))
            && $module instanceof Plugg_XOOPSCube_SearchableModule
        ) {
            return $module->searchCountContents();
        }

        return false;
    }

    public function searchFetchContentsSince($searchName, $timestamp, $limit, $offset)
    {
        $contents = array();
        if (($module = $this->_isModulePluggable($searchName))
            && $module instanceof Plugg_XOOPSCube_SearchableModule
        ) {
            $contents = $module->searchFetchContentsSince($timestamp, $limit, $offset);
        }

        return new ArrayObject($contents);
    }

    public function searchCountContentsSince($searchName, $timestamp)
    {
        if (($module = $this->_isModulePluggable($searchName))
            && $module instanceof Plugg_XOOPSCube_SearchableModule
        ) {
            return $module->searchCountContentsSince($timestamp);
        }
        
        return false;
    }

    public function searchFetchContentsByIds($searchName, $contentIds)
    {
        $contents = array();
        if (($module = $this->_isModulePluggable($searchName))
            && $module instanceof Plugg_XOOPSCube_SearchableModule
        ) {
            $contents = $module->searchFetchContentsByIds($contentIds);
        }

        return new ArrayObject($contents);
    }
    
    /* End implementation of Plugg_Search_Searchable */
    
    /* Start implementation of Plugg_Mail_Mailer */

    public function mailGetNicename()
    {
        return $this->_('PHPMailer included in XOOPS Cube');
    }

    public function mailGetSender()
    {
        $mailer = getMailer();
        $mailer->useMail();

        // Set deafult sender
        $mailer->setFromEmail($this->_application->SiteEmail());
        $mailer->setFromName($this->_application->SiteName());

        return new Plugg_XOOPSCube_MailSender($mailer);
    }

    public function mailGetSettings()
    {
        return array();
    }
    
    /* End implementation of Plugg_Mail_Mailer */

    function userMenuGetNames()
    {
        return array('notifications', 'pm');
    }

    function userMenuGetNicename($menuName)
    {
        switch ($menuName) {
            case 'notifications':
                return $this->_('Notifications');
            case 'pm':
                return $this->_('Private messages');
        }
    }

    function userMenuGetLinkText($menuName, Sabai_User $user)
    {
        switch ($menuName) {
            case 'notifications':
                return $this->_('Notifications');
            case 'pm':
                return $this->_('Private messages');
        }
    }

    function userMenuGetLinkUrl($menuName, Sabai_User $user)
    {
        switch ($menuName) {
            case 'notifications':
                return XOOPS_URL . '/notifications.php';
            case 'pm':
                return XOOPS_URL . '/viewpmsg.php';
        }
    }
    
    public function onFormBuildSystemAdminSettings($form)
    {
        $form['System']['display']['xoopscube_menu_items'] = array(
            '#title' => $this->_('Menu settings'),
            '#description' => $this->_('Select plugins to be displayed as sub menu items in XOOPS Cube main menu block.'),
            '#type' => 'checkboxes',
            '#tree' => false,
            '#options' => $menu_items = $this->getMenuItems(),
            '#default_value' => in_array('@all', $active_menu_items = (array)$this->getConfig('menu_items')) ? array_keys($menu_items) : $active_menu_items,
            '#weight' => '-5',
        );
        
        // Add callback called upon sumission of the form
        $form['#submit'][] = array($this, 'submitSystemAdminSettings');
    }
    
    public function submitSystemAdminSettings($form)
    {
        $this->saveConfig(array(
            'menu_items' => empty($form->values['xoopscube_menu_items']) ? array() : $form->values['xoopscube_menu_items']
        ));
    }
    
    public function getMenuItems()
    {
        $items = array();
        $routes = $this->_application->getPlugin('System')->getModel()->Route->criteria()->depth_is(1)->fetch();
        foreach ($routes as $route) {
            if (strlen($route->title) === 0 || $route->type != Plugg::ROUTE_TAB) continue;
        
            $items[$route->path] = $route->title;
        }
        
        return $items;
    }
    
    public function onFormBuildSearchAdminSystemSettings($form)
    {
        $form[$this->_name] = array(
            '#title' => $this->_('XOOPS Cube module search settings'),
            '#collapsible' => true,
            '#tree' => true,
            'useXoopsSearch' => array(
                '#type' => 'checkbox',
                '#title' => $this->_('Do not redirect to Plugg search from the default XOOPS Cube search page.'),
                '#description' => $this->_('Check this option if you do not want users to be redirected to Plugg search when the search page of XOOPS Cube is requested. This is useful when you have many XOOPS Cube modules that do not yet implement the Plugg search interface.'),
                '#default_value' => $this->getConfig('useXoopsSearch') ? 1 : 0,
            )
        );
        $options = $options_disabled = $attributes = array();
        foreach ($this->_getPluggableModules() as $module_dirname => $search) {
            if (is_object($search)) {
                $options[$module_dirname] = array(
                    'module' => sprintf('%s - %s', $search->name, $search->module),
                    'status' => $this->_('Module is installed and searchable.'),
                );
            } else {
                if ($module = xoops_gethandler('module')->getByDirname($module_dirname)) {
                    $options[$module_dirname] = array(
                        'module' => $module_dirname,
                        'status' => $this->_('Module is installed and searchable.')
                    );
                } else {
                    $options[$module_dirname] = array(
                        'module' => $module_dirname,
                        'status' => $this->_('Module is searchable but not installed.')
                    );
                    $options_disabled[] = $module_dirname;
                    $attributes[$module_dirname]['@row']['class'] = 'shadow';
                }
            }
        }
        $form[$this->_name]['searchableModules'] = array(
            '#title' => $this->_('Searchable modules'),
            '#description' => $this->_('The following modules are currently searchable by the Plugg search engine. If your modules are not listed, that means those modules do not yet implement the Plugg search interface.'),
            '#type' => 'tableselect',
            '#multiple' => true,
            '#header' => array('module' => $this->_('Module'), 'status' => $this->_('Description')),
            '#options' => $options,
            '#options_disabled' => $options_disabled,
            '#default_value' => array_keys($this->_getSearches()),
            '#attributes' => $attributes,
            '#disabled' => true,
        );

        // Add callback called upon sumission of the form
        $form['#submit'][] = array($this, 'submitSearchAdminSystemSettings');
    }

    public function submitSearchAdminSystemSettings($form)
    {
        if (!empty($form->values[$this->_name])) {
            $this->saveConfig($form->values[$this->_name]);
        }
    }
    
    public function getDefaultConfig()
    {
        return array('menu_items' => array('@all'), 'useXoopsSearch' => false);
    }
}
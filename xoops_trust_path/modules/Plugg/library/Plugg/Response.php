<?php
class Plugg_Response extends Sabai_Application_WebResponse
{
    private $_pageInfo = array(), $_pageTitle, $_pageMenu = array(), $_tabs = array(),
        $_currentTabSet = 0, $_currentTab, $_tabPageInfo = array(), $_tabPageTitle, $_tabPageMenu = array(),
        $_navigationEnabled = true, $_breadcrumbsEnabled = true;
        
    public function isAjax()
    {
        return false;
    }
        
    protected function _renderContent($content)
    {
        // Add navigations to the content?
        if ($this->_navigationEnabled) {
            // Set page and tab data
            $page_breadcrumbs = array();
            if ($this->_breadcrumbsEnabled && !empty($this->_pageInfo)) {
                $page_breadcrumbs = $this->_pageInfo;
                if (!isset($this->_pageTitle)) {
                    $page_info_last = array_pop($this->_pageInfo);
                    $this->_pageTitle = $page_info_last['title'];
                    if (!isset($this->_htmlHeadTitle)) $this->_htmlHeadTitle = $this->_pageTitle;
                }
            }
            $vars = array(
                'CONTENT' => $content,
                'PAGE_TITLE' => $this->_pageTitle,
                'PAGE_MENU' => $this->_pageMenu,
                'PAGE_BREADCRUMBS' => $page_breadcrumbs,
                'TAB_CURRENT' => $this->_currentTab,
                'TABS' => $this->_tabs,
                'TAB_PAGE_TITLE' => $this->_tabPageTitle,
                'TAB_PAGE_MENU' => $this->_tabPageMenu,
                'TAB_PAGE_BREADCRUMBS' => $this->_tabPageInfo,
            );
            
            $navigation_file = $this->_layoutDir . '/navigation' . $this->_layoutFileEx;

            ob_start();
            $this->_include($navigation_file, $vars);
            $content = ob_get_clean();
        }

        return parent::_renderContent($content);
    }

    public function renderPluginTemplate(Plugg_Plugin $plugin, $template, array $vars, $priority = 0)
    {
        $ret = '';
        $this->addTemplateDir($plugin->path . '/templates', $priority);
        if ($template_path = $this->getTemplatePath($template)) {
            $current_plugin = $this->getPlugin();
            $this->setCurrentPlugin($plugin);
            $ret = $this->renderTemplateFile($template_path, $vars);
            $this->setCurrentPlugin($current_plugin);
        }
        array_shift($this->_templateDir[$priority]);

        return $ret;
    }
    
    public function setNavigationEnabled($flag = true)
    {
        $this->_navigationEnabled = (bool)$flag;
        
        return $this;
    }
    
    public function setBreadcrumbsEnabled($flag = true)
    {
        $this->_breadcrumbsEnabled = (bool)$flag;
        
        return $this;
    }

    public function getPageInfo()
    {
        return $this->_pageInfo;
    }

    public function setPageInfo($title, $url = array(), $ajax = true)
    {
        if (empty($this->_tabs) || empty($this->_currentTab)) {
            $this->_pageInfo[] = array(
                'title' => $title,
                'url' => $url,
            );
        } else {
            $this->_tabPageInfo[$this->_currentTabSet][] = array(
                'title' => $title,
                'url' => $url,
                'ajax' => $ajax,
            );
        }

        return $this;
    }

    public function popPageInfo()
    {
        if (empty($this->_tabs) || empty($this->_currentTab)) {
            return array_pop($this->_pageInfo);
        }

        return array_pop($this->_tabPageInfo[$this->_currentTabSet]);
    }

    public function getPageTitle()
    {
        return $this->_pageTitle;
    }

    public function setPageTitle($title, $topPageTitle = false, $setHtmlHeadTitle = true)
    {
        if ($topPageTitle || empty($this->_tabs) || empty($this->_currentTab)) {
            $this->_pageTitle = $title;
            if ($setHtmlHeadTitle) $this->setHtmlHeadTitle($title);
        } else {
            $this->_tabPageTitle[$this->_currentTabSet] = $title;
        }

        return $this;
    }

    public function getPageMenu()
    {
        return $this->_pageMenu;
    }

    public function setPageMenu(array $menu)
    {
        if (empty($this->_tabs) || empty($this->_currentTab)) {
            $this->_pageMenu[] = $menu;
        } else {
            $this->_tabPageMenu[$this->_currentTabSet][] = $menu;
        }

        return $this;
    }

    public function popPageMenu()
    {
        if (empty($this->_tabs) || empty($this->_currentTab)) {
            return array_pop($this->_pageMenu);
        }

        return array_pop($this->_tabPageMenu[$this->_currentTabSet]);
    }

    public function addTabSet(array $tabs)
    {
        $this->_tabs[++$this->_currentTabSet] = $tabs;
        return $this;
    }

    public function removeTabSet($preservePageInfo = false)
    {
        if ($preservePageInfo) {
            // Get previously selected tab info
            $previous_tab = $this->_currentTab[$this->_currentTabSet];
            $pageinfo = $this->_tabs[$this->_currentTabSet][$previous_tab];
        }
        unset(
            $this->_tabs[$this->_currentTabSet],
            $this->_currentTab[$this->_currentTabSet],
            $this->_tabPageInfo[$this->_currentTabSet],
            $this->_tabPageTitle[$this->_currentTabSet],
            $this->_tabPageMenu[$this->_currentTabSet]
        );
        --$this->_currentTabSet;

        // Append previously selected tab info to page info
        if (!empty($pageinfo)) {
            $this->setPageInfo($pageinfo['title'], $pageinfo['url'], $pageinfo['ajax']);
        }

        return $this;
    }

    public function setCurrentTab($tabName)
    {
        if (isset($this->_tabs[$this->_currentTabSet][$tabName])) {
            $this->_currentTab[$this->_currentTabSet] = $tabName;
        }
        return $this;
    }

    public function clearPageInfo()
    {
        $this->_pageInfo = $this->_pageMenu = $this->_tabPageInfo = $this->_tabPageMenu = array();
        $this->_pageTitle = $this->_tabPageTitle = null;

        return $this;
    }

    public function setLoginRequiredError($flag = true)
    {
        $login_url = array(
            'script' => 'main',
            'base' => '/user/login/',
            'params' => array('return' => 1)
        );
        
        $this->_isSending = true;
        
        $this->setError($this->_('You must login to perform this operation'), $login_url);
        
        $this->_isSending = false;

        return $this;
    }
    
    public function isFeed($charset = 'UTF-8', $contentType = 'text/xml')
    {
        $this->setLayoutEnabled(false)->setNavigationEnabled(false)->setCharset($charset)->setContentType($contentType);
    }
}
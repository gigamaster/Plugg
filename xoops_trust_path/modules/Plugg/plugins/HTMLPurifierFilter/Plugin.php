<?php
class Plugg_HTMLPurifierFilter_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Admin, Plugg_Filters_Filter
{
    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/filter/' . $this->_name => array(
                'controller' => 'Filter_Index',
            ),
            '/filter/' . $this->_name . '/submit' => array(
                'controller' => 'Filter_Submit',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path){}

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType){}

    /* End implementation of Plugg_System_Routable_Admin */

    /* Start implementation of Plugg_Filters_Filter */

    public function filtersFilterGetNames()
    {
        return array('default' => $this->_('Filtered HTML'));
    }

    public function filtersFilterGetNicename($filterName)
    {
        return $this->_('Filtered HTML');
    }

    public function filtersFilterGetSummary($filterName)
    {
        return $this->_('Uses the HTMLPurifier library to purify and filter user submitted HTML.');
    }

    public function filtersFilterText($filterName, $text)
    {
        // Convert quoted text to HTML
        $html = $this->_application->getPlugin('Filters')->filterQuotedText($text, true);

        $options = array_merge($this->_application->getLocator()->getDefaultParam('HTMLPurifierConfig', 'options'), array(
            'HTML.DefinitionID' => $this->name,
            'Attr.EnableID' => true,
            'URI.DisableExternalResources' => (bool)$this->getConfig('uriDisableExternalResources'),
            'AutoFormat.Linkify' => (bool)$this->getConfig('autoFormatLinkify'),
            'AutoFormat.AutoParagraph' => (bool)$this->getConfig('autoFormatAutoParagraph'),
            'HTML.AllowedElements' => $this->getConfig('htmlAllowedElements'),
            'Filter.Custom' => $this->_getCustomFilters()
        ));
        // Merge custom config options with the default
        $config = $this->_application->getLocator()->createService('HTMLPurifierConfig', array(
            'options' => $options
        ));
        $htmlpurifier = $this->_application->getLocator()->createService('HTMLPurifier', array(
            'HTMLPurifierConfig' => $config
        ));

        return $htmlpurifier->purify($html);
    }

    public function filtersFilterGetTips($filterName, $long)
    {
        $tips = array();
        if ($this->getConfig('autoFormatLinkify')) {
            $tips[] = $this->_('Auto-linking is enabled. URLs(http, ftp, and https) will be converted to HTML links.');
        }
        if ($this->getConfig('autoFormatAutoParagraph')) {
            $tips[] = $this->_('Auto-paragraphing is enabled. Double newlines will be converted to paragraphs; for single newlines, use the pre or br tags.');
        }
        if ($htmlAllowedElements = $this->getConfig('htmlAllowedElements')) {
            $tips[] = sprintf('%s: %s', $this->_('Allowed HTML tags'), implode(', ', $htmlAllowedElements));
        }

        return $tips;
    }

    public function filtersFilterGetSettings($filterName)
    {
        return array(
            'uriDisableExternalResources' => array(
                '#title' => $this->_('Disable external resources.'),
                '#description' => $this->_('Check this option to disable the embedding of external resources, preventing users from embedding things like images from other hosts.'),
                '#required' => true,
                '#type' => 'checkbox',
                '#default_value' => $this->getConfig('uriDisableExternalResources'),
            ),
            'autoFormatLinkify' => array(
                '#title' => $this->_('Enable auto-linkify.'),
                '#description' => $this->_('Check this option to automagically convert URLs in user posts to HTML links.'),
                '#required' => true,
                '#type' => 'checkbox',
                '#default_value' => $this->getConfig('autoFormatLinkify'),
            ),
            'autoFormatAutoParagraph' => array(
                '#title' => $this->_('Enable auto-paragraph.'),
                '#description' => $this->_('Check this option to convert double newlines in user posts to paragraphs. p tags must be allowed for this directive to take effect. We do not use br tags for paragraphing, as that is semantically incorrect.'),
                '#default_value' => $this->getConfig('autoFormatAutoParagraph'),
                '#required' => true,
                '#type' => 'checkbox',
            ),
            'htmlAllowedElements' => array(
                '#title' => $this->_('Allowed HTML tags'),
                '#description' => $this->_('HTML tags allowed to be used. Separate tags with a comma. If you are not sure what to enter here, it is recommended that you leave this option as-is.'),
                '#default_value' => $this->getConfig('htmlAllowedElements'),
                '#required' => true,
                '#type' => 'textmulti',
                '#separator' => ',',
                '#rows' => 5,
            ),
        );
    }

    private function _getCustomFilters()
    {
        $ret = array();
        $filters = $this->getModel()->Customfilter->criteria()->active_is(1)->fetch('order');
        foreach ($filters as $filter) {
            if ($plugin = $this->_application->getPlugin($filter->plugin)) {
                $ret[] = $plugin->htmlpurifierfilterCustomFilterGetInstance($filter->name);
            }
        }

        return $ret;
    }

    /* End implementation of Plugg_Filters_Filter */

    /**
     * Called when a plugin that implements the Plugg_Filters_Filter interface is installed
     */
    public function onHTMLPurifierFilterCustomFilterInstalled($pluginEntity, $plugin)
    {
        if ($filters = $plugin->htmlpurifierfilterCustomFilterGetNames()) {
            $this->_createCustomFilters($pluginEntity->name, $filters);
        }
    }

    /**
     * Called when a plugin that implements the Plugg_Filters_Filter interface is uninstalled
     */
    public function onHTMLPurifierFilterCustomFilterUninstalled($pluginEntity, $plugin)
    {
        $this->_deleteCustomFilters($pluginEntity->name);
    }

    /**
     * Called when a plugin that implements the Plugg_Filters_Filter interface is upgraded
     */
    public function onHTMLPurifierFilterCustomFilterUpgraded($pluginEntity, $plugin)
    {
        // Update filters
        if (!$filters = $plugin->htmlpurifierfilterCustomFilterGetNames()) {
            $this->_deleteCustomFilters($pluginEntity->name);
        } else {
            $model = $this->getModel();
            $filters_already_installed = array();
            foreach ($model->Customfilter->criteria()->plugin_is($plugin_name)->fetch() as $current_filter) {
                if (in_array($current_filter->name, $filters)) {
                    $filters_already_installed[] = $current_filter->name;
                } else {
                    $current_filter->markRemoved();
                }
            }
            $this->_createCustomFilters($plugin_name, array_diff($filters, $filters_already_installed));
        }
    }

    private function _createCustomFilters($pluginName, $filters)
    {
        $model = $this->getModel();
        foreach ($filters as $filter_name => $filter_title) {
            if (empty($filter_name)) continue;
            $filter = $model->create('Customfilter');
            $filter->name = $filter_name;
            $filter->plugin = $pluginName;
            $filter->active = 1;
            $filter->markNew();
        }

        return $model->commit();
    }

    private function _deleteCustomFilters($pluginName)
    {
        $model = $this->getModel();
        foreach ($model->Customfilter->criteria()->plugin_is($pluginName)->fetch() as $e) {
            $e->markRemoved();
        }

        return $model->commit();
    }
    
    public function getDefaultConfig()
    {
        return array(
            'cacheSerializerPath' => '',
            'uriDisableExternalResources' => true,
            'autoFormatLinkify' => true,
            'autoFormatAutoParagraph' => true,
            'htmlAllowedElements' => array('a', 'abbr', 'acronym', 'b', 'blockquote', 'br', 'caption', 'cite', 'code', 'dd', 'del', 'dfn', 'div', 'dl',
                 'dt', 'em', 'i', 'ins', 'kbd', 'li', 'ol', 'p', 'pre', 's', 'strike', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot',
                'th', 'thead', 'tr', 'tt', 'u', 'ul','var'
            ),
        );
    }
}
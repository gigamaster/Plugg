<?php
class Plugg_HTMLPurifierFilter_Admin_Filter_Index extends Sabai_Application_ModelEntityController_List
{
    private $_select;
    private $_sortBy;

    function __construct()
    {
        parent::__construct('Customfilter');
        $this->_defaultSort = 'order';
    }

    function _getCriteria(Sabai_Request $request)
    {
        $this->_select = $request->asStr('select');
        switch($this->_select) {
            case 'active':
                $criteria = Sabai_Model_Criteria::createValue('customfilter_active', 1);
                break;
            case 'inactive':
                $criteria = Sabai_Model_Criteria::createValue('customfilter_active', 0);
                break;
            default:
                $this->_select = 'all';
                $criteria = false;
                break;
        }
        return $criteria;
    }

    protected function _getRequestedSort($request)
    {
        if ($sort_by = $request->asStr('sortby')) {
            $sort_by = explode(',', $sort_by);
            if (count($sort_by) == 2) {
                $this->_sortBy = $sort_by;
                return $this->_sortBy[0];
            }
        }
    }

    protected function _getRequestedOrder($request)
    {
        return isset($this->_sortBy[1]) ? $this->_sortBy[1] : null;
    }

    function _onListEntities($entities, Sabai_Request $request)
    {
        $custom_filter_names = array();
        foreach ($entities as $custom_filter) {
            $plugin_name = $custom_filter->plugin;
            if ($plugin = $this->getPlugin($plugin_name)) {
                $custom_filter_name = $custom_filter->name;
                $plugin_nicename = $plugin->nicename;

                // Abuse the HTMLPurifierFilterAdminRoutes event to get admin routes for this plugin
                $admin_routes = array();
                $method = 'onHTMLPurifierFilterAdminRoutes';
                if (method_exists($plugin, $method)) {
                    // Use call_user_func_array to pass by reference
                    call_user_func_array(array($plugin, $method), array(&$admin_routes));
                }

                $custom_filter_names[$plugin_name][$custom_filter_name] = array(
                    'nicename' => sprintf($plugin->htmlpurifierfilterCustomFilterGetNicename($custom_filter_name), $plugin_nicename),
                    'plugin_nicename' => $plugin_nicename,
                    'admin_routes' => $admin_routes
                );
            }
        }
        
        
        
        $response->setContent(array(
            'custom_filter_names' => $custom_filter_names,
            'requested_select' => $this->_select,
        ));
        return $entities;
    }

    protected function _getModel(Sabai_Request $request)
    {
        return $this->getPluginModel();
    }
}
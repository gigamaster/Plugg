<?php
class Plugg_Application_XOOPSCubeLegacy_AdminRoles extends Plugg_Form_Controller
{
    private $_moduleId, $_modulePerm;
    
    public function __construct($module)
    {
        $this->_moduleId = $module->getVar('mid');
        $this->_modulePerm = $module->getVar('dirname') . '_role';
    }
    
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Manually invoke the AdminControllerEnter event.
        $this->DispatchEvent('AdminControllerEnter', array($request, $response));
        
        parent::_doExecute($request, $response);
        
        // Manually invoke the AdminControllerExit event.
        $this->DispatchEvent('AdminControllerExit', array($request, $response));
    }
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_cancelUrl = null;
        $response->setPageTitle(_MD_A_PLUGG_XROLES);
        
        $groupperm_h = xoops_gethandler('groupperm');
        $form = array('roles' => array('#type' => 'fieldset', '#tree' => true));
        $roles = $this->_getPluggRoles();
        foreach (xoops_gethandler('member')->getGroupList() as $group_id => $group_name) {
            if ($group_id == XOOPS_GROUP_ANONYMOUS) continue;
            
            $form['roles'][$group_id] = array(
                '#type' => 'checkboxes',
                '#title' => $group_name,
                '#options' => $roles,
            );
            if ($group_id == XOOPS_GROUP_ADMIN || $groupperm_h->checkRight('module_admin', $this->_moduleId, $group_id)) {
                // Module administrators have all roles assigned and cannot be modified
                $form['roles'][$group_id]['#value'] = array_keys($roles);
                $form['roles'][$group_id]['#disabled'] = true;
            } elseif ($current_roles = $groupperm_h->getItemIds($this->_modulePerm, $group_id, $this->_moduleId)) {
                $form['roles'][$group_id]['#default_value'] = $current_roles;
            }
        }

        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $groupperm_h = xoops_gethandler('groupperm');
        
        // Reset permissions for this module
        if (!$groupperm_h->deleteByModule($this->_moduleId, $this->_modulePerm)) return false;
        
        foreach ($form->values['roles'] as $group_id => $role_ids) {
            if (in_array($group_id, array(XOOPS_GROUP_ANONYMOUS, XOOPS_GROUP_ADMIN))
                || $groupperm_h->checkRight('module_admin', $this->_moduleId, $group_id)
            ) {
                continue;
            }
            foreach ($role_ids as $role_id) {
                $groupperm_h->addRight($this->_modulePerm, $role_id, $group_id, $this->_moduleId);
            }
        }
        
        return true;
    }

    private function _getPluggRoles()
    {
        $roles = array();
        foreach ($this->getPlugin('User')->getModel()->Role->fetch() as $role) {
            $roles[$role->id] = $role->display_name;
        }

        return $roles;
    }
}
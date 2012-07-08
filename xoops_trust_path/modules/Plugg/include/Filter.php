<?php
class Plugg_Application_XOOPSCubeLegacy_Filter extends Sabai_Application_ControllerFilter
{
    public function before(Sabai_Request $request, Sabai_Application_Response $response, Sabai_Application $application)
    {
        $application->addHelperDir(XOOPS_TRUST_PATH . '/modules/Plugg/helpers');

        if (empty($GLOBALS['xoopsUser'])
            || $application->getUser()->isFinalized()
            || $application->getUser()->isSuperUser()
        ) return;

        $xoops_groups = $GLOBALS['xoopsUser']->getGroups();
        // Set as super user if belongs to the default admin group
        if (in_array(XOOPS_GROUP_ADMIN, $xoops_groups)) {
            $application->getUser()->setSuperUser(true);
            $application->getUser()->finalize(true);

            return;
        }

        // Set as super user if module admin
        $module_id = $GLOBALS['xoopsModule']->getVar('mid');
        if (xoops_gethandler('groupperm')->checkRight('module_admin', $module_id, $xoops_groups)) {
            $application->getUser()->setSuperUser(true);
            $application->getUser()->finalize(true);

            return;
        }

        // Load roles and set permissions
        $perm = $GLOBALS['xoopsModule']->getVar('dirname') . '_role';
        if ($role_ids = xoops_gethandler('groupperm')->getItemIds($perm, $xoops_groups, $module_id)) {
            $roles = $application->getPluginModel('User')->Role->criteria()->id_in($role_ids)->fetch();
            foreach ($roles as $role) {
                foreach ($role->getPermissions() as $plugin_name => $permissions) {
                    if (empty($permissions)) continue;
                    foreach ($permissions as $permission) {
                        $application->getUser()->addPermission($permission);
                    }
                }
            }
        }

        $application->getUser()->finalize(true);
    }

    public function after(Sabai_Request $request, Sabai_Application_Response $response, Sabai_Application $application)
    {

    }
}
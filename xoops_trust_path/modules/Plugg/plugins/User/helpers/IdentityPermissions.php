<?php
class Plugg_Helper_User_IdentityPermissions extends Sabai_Application_Helper
{
    private $_userPermissions = array();

    /**
     * Creates an image HTML link to user profile page
     *
     * @return string
     * @param Sabai_Application $application
     * @param Sabai_User_Identity $identity
     * @param string $rel
     */
    public function help(Sabai_Application $application, Sabai_User_AbstractIdentity $identity, $permissionsToCheck = null)
    {
        $id = $identity->id;

        // Check if cached
        if (isset($this->_userPermissions[$id])) return $this->_userPermissions[$id];

        if (true === $role_ids = $application->getPlugin('User')->getManagerPlugin()->userGetRoleIdsById($id)) {
            // Returning true means all roles and permissions
            $this->_userPermissions[$id] = true;
            
            return true;
        }

        $model = $application->getPlugin('User')->getModel();
        foreach ($model->Member->fetchByUser($id) as $member) $role_ids[] = $member->role_id;

        $user_permissions = array();
        if (!empty($role_ids)) {
            foreach ($model->Role->criteria()->id_in($role_ids)->fetch() as $role) {
                foreach ($role->getPermissions() as $plugin_name => $permissions) {
                    if (empty($permissions)) continue;
                    foreach ($permissions as $permission) $user_permissions[] = $permission;
                }
            }
        }
        $this->_userPermissions[$id] = array_unique($user_permissions);

        // If there are no permissions to ckeck, return all the permissions for the user
        if (empty($permissionsToCheck)) return $this->_userPermissions[$id];
        
        // Check permissions
        foreach ((array)$permissionsToCheck as $perm) {
            foreach ((array)$perm as $_perm) {
                if (!in_array($_perm, $this->_userPermissions[$id])) continue 2;
            }

            return true;
        }

        return false;
    }
}
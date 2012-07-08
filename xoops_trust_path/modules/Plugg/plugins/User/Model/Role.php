<?php
class Plugg_User_Model_Role extends Plugg_User_Model_Base_Role
{
    function getPermissions()
    {
        return ($permissions = @unserialize($this->permissions)) ? $permissions : array();
    }

    function setPermissions($permissions)
    {
        $this->permissions = serialize($permissions);
    }
    
    public function addPermission($plugin, $permission)
    {
        $permissions = $this->getPermissions();
        if (!isset($permissions[$plugin])) $permissions[$plugin] = array();
        foreach ((array)$permission as $_permission) {
            if (!in_array($_permission, $permissions[$plugin])) {
                $permissions[$plugin][] = $_permission;
            }
        }
        $this->setPermissions($permissions);
    }
    
    public function resetPermissions($plugin)
    {
        $permissions = $this->getPermissions();
        if (isset($permissions[$plugin])) {
            $permissions[$plugin] = array();
        }
        $this->setPermissions($permissions);
    }
    
    public function hasPermission($plugin, $permission)
    {
        $permissions = $this->getPermissions();
        
        return isset($permissions[$plugin]) && in_array($permission, $permissions[$plugin]);
    }
}

class Plugg_User_Model_RoleRepository extends Plugg_User_Model_Base_RoleRepository
{
}
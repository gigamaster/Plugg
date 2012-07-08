<?php
class Plugg_User_Model_MembersWithRole extends Sabai_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('role_id', 'Role', 'role_id', $collection);
    }
}
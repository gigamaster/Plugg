<?php
class Plugg_User_Model_RolesWithMembers extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('member_role_id', 'Member', $collection);
    }
}
<?php
class Plugg_User_Model_RolesWithLastMember extends Sabai_Model_EntityCollection_Decorator_ForeignEntitiesLast
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('member_last', 'Member', 'member_id', $collection);
    }
}
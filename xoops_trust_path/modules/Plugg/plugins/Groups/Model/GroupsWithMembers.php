<?php
class Plugg_Groups_Model_GroupsWithMembers extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('member_group_id', 'Member', $collection);
    }
}
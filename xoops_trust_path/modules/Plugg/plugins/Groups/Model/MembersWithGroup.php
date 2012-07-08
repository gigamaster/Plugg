<?php
class Plugg_Groups_Model_MembersWithGroup extends Sabai_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('group_id', 'Group', 'group_id', $collection);
    }
}
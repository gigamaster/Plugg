<?php
class Plugg_Forum_Model_CommentsWithParentsCount extends Sabai_Model_EntityCollection_Decorator_ParentEntitiesCount
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('Comment', $collection);
    }
}
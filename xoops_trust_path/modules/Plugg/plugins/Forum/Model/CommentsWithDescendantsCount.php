<?php
class Plugg_Forum_Model_CommentsWithDescendantsCount extends Sabai_Model_EntityCollection_Decorator_DescendantEntitiesCount
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('Comment', $collection);
    }
}
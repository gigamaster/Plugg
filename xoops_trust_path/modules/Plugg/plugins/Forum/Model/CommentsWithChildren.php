<?php
class Plugg_Forum_Model_CommentsWithChildren extends Sabai_Model_EntityCollection_Decorator_ChildEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('Comment', 'comment_parent', $collection);
    }
}
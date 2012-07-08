<?php
class Plugg_Forum_Model_TopicsWithLastComment extends Sabai_Model_EntityCollection_Decorator_ForeignEntitiesLast
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('comment_last', 'Comment', 'comment_id', $collection);
    }
}
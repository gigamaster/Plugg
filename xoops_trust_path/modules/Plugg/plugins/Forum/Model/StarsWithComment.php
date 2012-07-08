<?php

class Plugg_Forum_Model_StarsWithComment extends Sabai_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('comment_id', 'Comment', 'comment_id', $collection);
    }
}
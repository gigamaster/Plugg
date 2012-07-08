<?php

class Plugg_Forum_Model_CommentsWithStars extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('star_comment_id', 'Star', $collection);
    }
}
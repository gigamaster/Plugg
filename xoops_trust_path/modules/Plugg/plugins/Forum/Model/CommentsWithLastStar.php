<?php

class Plugg_Forum_Model_CommentsWithLastStar extends Sabai_Model_EntityCollection_Decorator_ForeignEntitiesLast
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('star_last', 'Star', 'star_id', $collection);
    }
}
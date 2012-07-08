<?php

class Plugg_Forum_Model_TopicsWithStars extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('star_topic_id', 'Star', $collection);
    }
}
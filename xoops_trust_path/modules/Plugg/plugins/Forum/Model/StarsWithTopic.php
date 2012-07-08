<?php

class Plugg_Forum_Model_StarsWithTopic extends Sabai_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('topic_id', 'Topic', 'topic_id', $collection);
    }
}
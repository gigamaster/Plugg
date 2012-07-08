<?php

class Plugg_Forum_Model_TopicsWithViews extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('view_topic_id', 'View', $collection);
    }
}
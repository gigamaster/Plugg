<?php

class Plugg_Forum_Model_TopicsWithLastView extends Sabai_Model_EntityCollection_Decorator_ForeignEntitiesLast
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('view_last', 'View', 'view_id', $collection);
    }
}
<?php
class Plugg_Aggregator_Model_ItemsWithFeed extends Sabai_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('feed_id', 'Feed', 'feed_id', $collection);
    }
}
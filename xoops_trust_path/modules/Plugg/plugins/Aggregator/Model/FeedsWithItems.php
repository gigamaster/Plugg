<?php
class Plugg_Aggregator_Model_FeedsWithItems extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('item_feed_id', 'Item', $collection);
    }
}
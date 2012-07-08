<?php
class Plugg_Aggregator_Model_FeedsWithLastItem extends Sabai_Model_EntityCollection_Decorator_ForeignEntitiesLast
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('item_last', 'Item', 'item_id', $collection);
    }
}
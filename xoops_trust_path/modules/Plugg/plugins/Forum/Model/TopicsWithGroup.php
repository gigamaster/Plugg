<?php
class Plugg_Forum_Model_TopicsWithGroup extends Plugg_Groups_Model_WithGroup
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection);
    }
}
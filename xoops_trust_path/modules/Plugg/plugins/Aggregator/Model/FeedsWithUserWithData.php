<?php
class Plugg_Aggregator_Model_FeedsWithUserWithData extends Plugg_User_Model_WithUser
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection, true);
    }
}
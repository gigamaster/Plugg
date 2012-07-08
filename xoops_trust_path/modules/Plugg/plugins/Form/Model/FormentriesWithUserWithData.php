<?php
class Plugg_Form_Model_FormentriesWithUserWithData extends Plugg_User_Model_WithUser
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection, true);
    }
}
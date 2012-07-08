<?php
class Plugg_Messages_Model_MessagesWithUserWithData extends Plugg_User_Model_WithUser
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection, true);
    }
}
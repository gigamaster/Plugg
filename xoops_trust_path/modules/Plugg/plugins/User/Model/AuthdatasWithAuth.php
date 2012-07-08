<?php
class Plugg_User_Model_AuthdatasWithAuth extends Sabai_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('auth_id', 'Auth', 'auth_id', $collection);
    }
}
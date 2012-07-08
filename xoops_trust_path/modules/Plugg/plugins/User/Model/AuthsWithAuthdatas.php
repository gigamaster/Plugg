<?php
class Plugg_User_Model_AuthsWithAuthdatas extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('authdata_auth_id', 'Authdata', $collection);
    }
}
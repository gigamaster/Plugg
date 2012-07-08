<?php

class Plugg_Form_Model_FormfieldsWithField extends Sabai_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('field_id', 'Field', 'field_id', $collection);
    }
}
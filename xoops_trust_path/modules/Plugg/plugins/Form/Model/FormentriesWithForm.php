<?php

class Plugg_Form_Model_FormentriesWithForm extends Sabai_Model_EntityCollection_Decorator_ForeignEntity
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('form_id', 'Form', 'form_id', $collection);
    }
}
<?php

class Plugg_Form_Model_FormsWithFormentries extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('formentry_form_id', 'Formentry', $collection);
    }
}
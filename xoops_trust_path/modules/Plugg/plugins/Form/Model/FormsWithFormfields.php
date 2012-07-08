<?php

class Plugg_Form_Model_FormsWithFormfields extends Sabai_Model_EntityCollection_Decorator_ForeignEntities
{
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct('formfield_form_id', 'Formfield', $collection);
    }
}
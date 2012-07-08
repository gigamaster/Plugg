<?php
abstract class Plugg_Form_Model_WithField extends Sabai_Model_EntityCollection_Decorator
{
    protected $_fields;
    protected $_fieldIdVar;
    protected $_fieldEntityObjectVarName;

    public function __construct(Sabai_Model_EntityCollection $collection, $fieldIdVar = 'field_id', $fieldEntityObjectVarName = 'Field')
    {
        parent::__construct($collection);
        $this->_fieldIdVar = $fieldIdVar;
        $this->_fieldEntityObjectVarName = $fieldEntityObjectVarName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_fields)) {
            $this->_fields = array();
            if ($this->_collection->count() > 0) {
                $field_ids = array();
                while ($this->_collection->valid()) {
                    if ($field_id = $this->_collection->current()->{$this->_fieldIdVar}) $field_ids[] = $field_id;
                    $this->_collection->next();
                }
                if (!empty($field_ids)) {
                    $this->_fields = $this->_model->Form_Fields(array_unique($field_ids))->getArray();
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        if ($field_id = $current->{$this->_fieldIdVar}) {
            $current->setObject($this->_fieldEntityObjectVarName, $this->_fields[$field_id]);
        }

        return $current;
    }
}
<?php
abstract class Plugg_Groups_Model_WithGroup extends Sabai_Model_EntityCollection_Decorator
{
    protected $_groups;
    protected $_groupIdVar;
    protected $_groupEntityObjectVarName;

    public function __construct(Sabai_Model_EntityCollection $collection, $groupIdVar = 'group_id', $groupEntityObjectVarName = 'Group')
    {
        parent::__construct($collection);
        $this->_groupIdVar = $groupIdVar;
        $this->_groupEntityObjectVarName = $groupEntityObjectVarName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_groups)) {
            $this->_groups = array();
            if ($this->_collection->count() > 0) {
                $group_ids = array();
                while ($this->_collection->valid()) {
                    if ($group_id = $this->_collection->current()->{$this->_groupIdVar}) $group_ids[] = $group_id;
                    $this->_collection->next();
                }
                if (!empty($group_ids)) {
                    $this->_groups = $this->_model->Groups_Groups(array_unique($group_ids))->getArray();
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        if ($group_id = $current->{$this->_groupIdVar}) {
            $current->setObject($this->_groupEntityObjectVarName, $this->_groups[$group_id]);
        }

        return $current;
    }
}
<?php
abstract class Plugg_User_Model_WithUser extends Sabai_Model_EntityCollection_Decorator
{
    protected $_userIdentities, $_userKeyVar, $_userEntityObjectVarName, $_withData;

    public function __construct(Sabai_Model_EntityCollection $collection, $withData = false, $userKeyVar = 'user_id', $userEntityObjectVarName = 'User')
    {
        parent::__construct($collection);
        $this->_withData = $withData;
        $this->_userKeyVar = $userKeyVar;
        $this->_userEntityObjectVarName = $userEntityObjectVarName;
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_userIdentities)) {
            $this->_userIdentities = array();
            if ($this->_collection->count() > 0) {
                $user_ids = array();
                while ($this->_collection->valid()) {
                    $user_ids[] = $this->_collection->current()->{$this->_userKeyVar};
                    $this->_collection->next();
                }
                $this->_userIdentities = $this->_model->User_Identities(array_unique($user_ids), $this->_withData);
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $current->setObject($this->_userEntityObjectVarName, $this->_userIdentities[$current->{$this->_userKeyVar}]);

        return $current;
    }
}
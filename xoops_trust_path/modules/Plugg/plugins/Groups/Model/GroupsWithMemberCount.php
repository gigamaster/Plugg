<?php
class Plugg_Groups_Model_GroupsWithMemberCount extends Sabai_Model_EntityCollection_Decorator
{
    private $_memberStatus;
    private $_memberCount;
    private $_countVarName;

    public function __construct($collection, $memberStatus = Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE)
    {
        parent::__construct($collection);
        switch ($memberStatus) {
            case Plugg_Groups_Plugin::MEMBER_STATUS_PENDING:
                $this->_memberStatus = Plugg_Groups_Plugin::MEMBER_STATUS_PENDING;
                $this->_countVarName = 'PendingMember';
                break;
            case Plugg_Groups_Plugin::MEMBER_STATUS_INVITED:
                $this->_memberStatus = Plugg_Groups_Plugin::MEMBER_STATUS_INVITED;
                $this->_countVarName = 'InvitedMember';
                break;
            default:
                $this->_memberStatus = Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE;
                $this->_countVarName = 'ActiveMember';
                break;
        }
    }

    public function rewind()
    {
        $this->_collection->rewind();
        if (!isset($this->_memberCount)) {
            $this->_memberCount = array();
            if ($this->_collection->count() > 0) {
                $criteria = $this->_model->createCriteria('Member')
                    ->status_is($this->_memberStatus)
                    ->groupId_in($this->_collection->getAllIds());
                $fields = array('member_group_id', 'COUNT(*)');
                if ($rs = $this->_model->getGateway('Member')->selectByCriteria($criteria, $fields, 0, 0, null, null, 'member_group_id')) {
                    while ($row = $rs->fetchRow()) {
                        $this->_memberCount[$row[0]] = $row[1];
                    }
                }
                $this->_collection->rewind();
            }
        }
    }

    public function current()
    {
        $current = $this->_collection->current();
        $count = isset($this->_memberCount[$current->id]) ? $this->_memberCount[$current->id] : 0;
        $current->setCount($this->_countVarName, $count);

        return $current;
    }
}
<?php
class Plugg_Messages_Model_Message extends Plugg_Messages_Model_Base_Message
{
    function markStarred($flag = true)
    {
        $this->star = intval($flag);
    }

    function isStarred()
    {
        return $this->star;
    }

    function markDeleted($flag = true)
    {
        $this->deleted = intval($flag);
    }

    function markRead($flag = true)
    {
        $this->read = intval($flag);
    }

    function isRead()
    {
        return $this->read;
    }

    function isOutgoing()
    {
        return $this->type == Plugg_Messages_Plugin::MESSAGE_TYPE_OUTGOING;
    }

    function isIncoming()
    {
        return $this->type == Plugg_Messages_Plugin::MESSAGE_TYPE_INCOMING;
    }

    function setOutgoing()
    {
        $this->type = Plugg_Messages_Plugin::MESSAGE_TYPE_OUTGOING;
    }

    function setIncoming()
    {
        $this->type = Plugg_Messages_Plugin::MESSAGE_TYPE_INCOMING;
    }
}

class Plugg_Messages_Model_MessageRepository extends Plugg_Messages_Model_Base_MessageRepository
{
}
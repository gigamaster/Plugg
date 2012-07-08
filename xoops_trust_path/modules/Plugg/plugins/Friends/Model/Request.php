<?php
class Plugg_Friends_Model_Request extends Plugg_Friends_Model_Base_Request
{
    function setPending()
    {
        $this->status = Plugg_Friends_Plugin::REQUEST_STATUS_PENDING;
    }

    function setRejected()
    {
        $this->status = Plugg_Friends_Plugin::REQUEST_STATUS_REJECTED;
    }

    function isPending()
    {
        return $this->status == Plugg_Friends_Plugin::REQUEST_STATUS_PENDING;
    }

    function isRejected()
    {
        return $this->status == Plugg_Friends_Plugin::REQUEST_STATUS_REJECTED;
    }
}

class Plugg_Friends_Model_RequestRepository extends Plugg_Friends_Model_Base_RequestRepository
{
}
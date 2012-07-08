<?php
class Plugg_Groups_Model_Member extends Plugg_Groups_Model_Base_Member
{
    public function isPending()
    {
        return $this->status == Plugg_Groups_Plugin::MEMBER_STATUS_PENDING;
    }

    public function isInvited()
    {
        return $this->status == Plugg_Groups_Plugin::MEMBER_STATUS_INVITED;
    }

    public function isActive()
    {
        return $this->status == Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE;
    }

    public function setActive()
    {
        $this->status = Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE;
    }

    public function isAdmin()
    {
        return $this->role == Plugg_Groups_Plugin::MEMBER_ROLE_ADMINISTRATOR;
    }

    public function getAddForm()
    {

    }
}

class Plugg_Groups_Model_MemberRepository extends Plugg_Groups_Model_Base_MemberRepository
{
}
<?php
class Plugg_Groups_Model_Group extends Plugg_Groups_Model_Base_Group
{
    private $_isMemberCache = array();
    private $_isActiveMemberCache = array();
    private $_activeMemberCount;

    public function isApproved()
    {
        return $this->status == Plugg_Groups_Plugin::GROUP_STATUS_APPROVED;
    }

    public function setApproved()
    {
        $this->status = Plugg_Groups_Plugin::GROUP_STATUS_APPROVED;
    }

    public function isMember($user)
    {
        if (!isset($this->_isMemberCache[$this->id])) {
            $count = $this->_model->Member->criteria()
                ->userId_is($user->id)
                ->countByGroup($this->id);
            $this->_isMemberCache[$this->id] = $count > 0;
        }

        return $this->_isMemberCache[$this->id];
    }

    public function isActiveMember($user)
    {
        if (!isset($this->_isActiveMemberCache[$this->id])) {
            $count = $this->_model->Member->criteria()
                ->userId_is($user->id)
                ->status_is(Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE)
                ->countByGroup($this->id);
            $this->_isActiveMemberCache[$this->id] = $count > 0;
        }

        return $this->_isActiveMemberCache[$this->id];
    }

    public function getActiveMemberCount()
    {
        if (!isset($this->_activeMemberCount)) {
            $this->_activeMemberCount = $this->_model->Member->criteria()
                ->status_is(Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE)
                ->countByGroup($this->id);
        }

        return $this->_activeMemberCount;
    }

    public function getAdministrators($limit = 0, $offset = 0, $sort = 'created', $order = 'ASC')
    {
        return $this->_model->Member->criteria()
            ->role_is(Plugg_Groups_Plugin::MEMBER_ROLE_ADMINISTRATOR)
            ->status_is(Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE)
            ->fetchByGroup($this->id, $limit, $offset, $sort, $order);
    }

    public function isInvitationRequired()
    {
        return $this->type == Plugg_Groups_Plugin::GROUP_TYPE_INVITATION_REQUIRED;
    }

    public function isApprovalRequired()
    {
        return $this->type == Plugg_Groups_Plugin::GROUP_TYPE_APPROVAL_REQUIRED;
    }

    public function getTypeStr()
    {
        $types = $this->_model->Groups_GroupTypes();
        return $types[$this->type];
    }

    public function getSummary($length = 500)
    {
        return mb_strimlength(strip_tags(strtr($this->description_html, array("\r" => '', "\n" => ''))), 0, $length);
    }
    
    public function getUrl($path = '', array $params = array(), $separator = '&amp;')
    {
        return $this->_model->Url(array(
            'base' => '/groups/' . $this->name,
            'script' => 'main',
            'path' => $path,
            'params' => $params,
            'separator' => $separator
        ));
    }
    
    public function getAvatarThumbnailUrl()
    {
        if ($this->avatar_thumbnail) {
            return $this->_model->ImageUrl('Groups', $this->avatar_thumbnail, 'avatars');
        }
            
        return $this->_model->Groups_GroupDefaultThumbnailUrl($this);
    }
    
    public function getAvatarUrl()
    {
        if ($this->avatar_medium) {
            return $this->_model->ImageUrl('Groups', $this->avatar_medium, 'avatars');
        }
            
        return $this->_model->Groups_GroupDefaultAvatarUrl($this);
    }
    
    public function getAvatarIconUrl()
    {
        if ($this->avatar_icon) {
            return $this->_model->ImageUrl('Groups', $this->avatar_icon, 'avatars');
        }
            
        return $this->_model->Groups_GroupDefaultIconUrl($this);
    }
    
    public function unlinkAvatars()
    {
        if ($this->avatar) @unlink(dirname(dirname(__FILE__))  . '/avatars/' . $this->avatar);
        if ($this->avatar_medium) @unlink(dirname(dirname(__FILE__))  . '/avatars/' . $this->avatar_medium);
        if ($this->avatar_thumbnail) @unlink(dirname(dirname(__FILE__))  . '/avatars/' . $this->avatar_thumbnail);
        if ($this->avatar_icon) @unlink(dirname(dirname(__FILE__))  . '/avatars/' . $this->avatar_icon);
        
        return $this;
    }
}

class Plugg_Groups_Model_GroupRepository extends Plugg_Groups_Model_Base_GroupRepository
{
}
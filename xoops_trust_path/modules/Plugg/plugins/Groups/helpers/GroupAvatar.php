<?php
class Plugg_Helper_Groups_GroupAvatar extends Sabai_Application_Helper
{
    private $_groupAvatars;
    
    /**
     * Creates a thumbnail image HTML link to the top page of a group
     *
     * @return string
     * @param Sabai_Application $application
     * @param Plugg_Groups_Model_Group $group
     * @param string $rel
     */
    public function help(Sabai_Application $application, Plugg_Groups_Model_Group $group, $rel = '')
    {
        $id = $group->id;
        if (!isset($this->_groupAvatars[$id])) {
            $this->_groupAvatars[$id] = sprintf(
                '<a href="%s" title="%s" rel="%s" class="group"><img src="%s" width="140" alt="" class="groups-group-avatar" style="margin:0; padding:0;" /></a>',
                $group->getUrl(), h($group->display_name), h($rel), $group->getAvatarUrl()
            );
        }

        return $this->_groupAvatars[$id];
    }
}
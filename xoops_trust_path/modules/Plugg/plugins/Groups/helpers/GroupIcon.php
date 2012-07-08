<?php
class Plugg_Helper_Groups_GroupIcon extends Sabai_Application_Helper
{
    private $_groupIcons;
    
    /**
     * Creates a small image HTML link to the top page of a group
     *
     * @return string
     * @param Sabai_Application $application
     * @param Plugg_Groups_Model_Group $group
     * @param string $rel
     */
    public function help(Sabai_Application $application, Plugg_Groups_Model_Group $group, $rel = '')
    {
        $id = $group->id;
        if (!isset($this->_groupIcons[$id])) {
            $this->_groupIcons[$id] = sprintf(
                '<a href="%s" title="%s" rel="%s" class="group"><img src="%s" width="16" alt="" class="groups-group-avatar" style="margin:0; padding:0;" /></a>',
                $group->getUrl(), h($group->display_name), h($rel), $group->getAvatarIconUrl()
            );
        }

        return $this->_groupIcons[$id];
    }
}
<?php
class Plugg_Helper_Groups_GroupThumbnail extends Sabai_Application_Helper
{
    private $_groupThumbnails;
    
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
        if (!isset($this->_groupThumbnails[$id])) {
            $this->_groupThumbnails[$id] = sprintf(
                '<a href="%s" title="%s" rel="%s" class="group"><img src="%s" width="48" alt="" class="groups-group-thumbnail" style="margin:0; padding:0;" /></a>',
                $group->getUrl(), h($group->display_name), h($rel), $group->getAvatarThumbnailUrl()
            );
        }

        return $this->_groupThumbnails[$id];
    }
}
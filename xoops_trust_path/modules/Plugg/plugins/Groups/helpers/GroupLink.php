<?php
class Plugg_Helper_Groups_GroupLink extends Sabai_Application_Helper
{
    private $_links;

    /**
     * Creates an HTML link to the top page of a group
     *
     * @return string
     * @param Sabai_Application $application
     * @param Plugg_Groups_Model_Group $group
     * @param string $rel
     */
    public function help(Sabai_Application $application, Plugg_Groups_Model_Group $group, $rel = '')
    {
        $id = $group->id;
        if (!isset($this->_links[$id])) {
            $this->_links[$id] = sprintf(
                '<a href="%1$s" rel="%2$s" title="%3$s">%4$s</a>',
                $group->getUrl(), h($rel), h($group->getSummary(150)), h($group->display_name)
            );
        }

        return $this->_links[$id];
    }
}
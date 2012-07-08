<?php
class Plugg_Helper_User_IdentityLink extends Sabai_Application_Helper
{
    private $_links;

    /**
     * Creates an HTML link to user profile page
     *
     * @return string
     * @param SabaiApplication $application
     * @param Sabai_User_Identity $identity
     * @param string $rel
     */
    public function help(Sabai_Application $application, Sabai_User_AbstractIdentity $identity, $rel = '')
    {
        if ($identity->isAnonymous()) return h($identity->name);

        $id = $identity->id;
        if (!isset($this->_links[$id])) {
            $this->_links[$id] = sprintf(
                '<a href="%1$s" rel="%3$s" class="plugg-user plugg-user-%5$d"><span class="plugg-user">%2$s</span><span class="plugg-user-self" style="display:none;">%4$s</span></a>',
                $application->User_IdentityUrl($identity), h($identity->display_name), h($rel), $application->_('You'), $identity->id
            );
        }

        return $this->_links[$id];
    }
}
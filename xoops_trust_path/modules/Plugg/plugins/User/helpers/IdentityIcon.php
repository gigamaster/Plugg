<?php
class Plugg_Helper_User_IdentityIcon extends Sabai_Application_Helper
{
    private $_userIcons;

    /**
     * Creates an image HTML link to user profile page
     *
     * @return string
     * @param SabaiApplication $application
     * @param Sabai_User_Identity $identity
     * @param string $rel
     */
    public function help(Sabai_Application $application, Sabai_User_AbstractIdentity $identity, $rel = '')
    {
        if (!$image = $identity->image_icon) return '';

        if ($identity->isAnonymous()) {
            return sprintf('<img src="%s" width="16" height="16" alt="%s" class="user userIcon" />', h($image), h($identity->name));
        }

        $id = $identity->id;
        if (!isset($this->_userIcons[$id])) {
            $this->_userIcons[$id] = sprintf(
                '<a href="%s" title="%s" rel="%s" class="user user%d"><img src="%s" width="16" height="16" alt="" class="user userIcon" style="margin:0; padding:0;" /></a>',
                $application->User_IdentityUrl($identity), h($identity->display_name), h($rel), $id, h($image)
            );
        }

        return $this->_userIcons[$id];
    }
}
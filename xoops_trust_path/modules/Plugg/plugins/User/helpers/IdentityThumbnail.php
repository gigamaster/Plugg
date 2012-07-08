<?php
class Plugg_Helper_User_IdentityThumbnail extends Sabai_Application_Helper
{
    private $_userThumbnails;

    /**
     * Creates an image HTML link to user profile page
     *
     * @return string
     * @param Sabai_Application $application
     * @param Sabai_User_Identity $identity
     * @param string $rel
     */
    public function help(Sabai_Application $application, Sabai_User_AbstractIdentity $identity, $rel = '')
    {
        if (!$image = $identity->image_thumbnail) return '';

        if ($identity->isAnonymous()) {
            return sprintf('<img src="%s" width="48" height="48" alt="%s" class="user userThumbnail" />', h($image), h($identity->name));
        }

        $id = $identity->id;
        if (!isset($this->_userThumbnails[$id])) {
            $this->_userThumbnails[$id] = sprintf(
                '<a href="%s" title="%s" rel="%s" class="user user%d"><img src="%s" width="48" height="48" alt="" class="user userThumbnail" style="margin:0; padding:0;" /></a>',
                $application->User_IdentityUrl($identity), h($identity->display_name), h($rel), $id, h($image)
            );
        }

        return $this->_userThumbnails[$id];
    }
}
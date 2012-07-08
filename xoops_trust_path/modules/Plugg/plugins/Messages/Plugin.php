<?php
class Plugg_Messages_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Main, Plugg_System_Routable_Admin, Plugg_User_Menu
{
    const MESSAGE_TYPE_INCOMING = 0;
    const MESSAGE_TYPE_OUTGOING = 1;

    /* Start implementation of Plugg_System_Routable_Main */

    public function systemRoutableGetMainRoutes()
    {
        return array(
            '/user/:user_id/messages' => array(
                'controller' => 'User_Index',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
                'access_callback' => true,
                'title_callback' => true,
            ),
            '/user/:user_id/messages/sent' => array(
                'controller' => 'User_Sent',
                'title' => $this->_('Sent messages'),
                'type' => Plugg::ROUTE_TAB,
                'weight' => 1
            ),
            '/user/:user_id/messages/new' => array(
                'controller' => 'User_NewMessage',
                'title' => $this->_('Compose message'),
                'type' => Plugg::ROUTE_TAB,
                'weight' => 2
            ),
            '/user/:user_id/messages/submit' => array(
                'controller' => 'User_Submit',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/user/:user_id/messages/:message_id' => array(
                'controller' => 'User_Message',
                'access_callback' => true,
                'format' => array(':message_id' => '\d+'),
            ),
            '/user/:user_id/messages/:message_id/reply' => array(
                'controller' => 'User_Message_Reply',
                'title' => $this->_('Reply'),
            ),
            '/user/:user_id/messages/:message_id/submit' => array(
                'controller' => 'User_Message_Submit',
                'type' => Plugg::ROUTE_CALLBACK,
            ),
            '/user/:user_id/send_message' => array(
                'controller' => 'User_SendMessage',
                'title' => $this->_('Send message'),
                'type' => Plugg::ROUTE_MENU,
                'access_callback' => true,
            ),
        );
    }

    public function systemRoutableOnMainAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/user/:user_id/messages':
                // Only the identity owner or super user has access
                return $this->_application->identity_is_me || $this->_application->getUser()->isSuperUser();

            case '/user/:user_id/messages/:message_id':
                // Make sure the requested message exists and belongs to the user
                return ($this->_application->message = $this->getRequestedEntity($request, 'Message', 'message_id'))
                    && $this->_application->message->isOwnedBy($this->_application->identity);

            case '/user/:user_id/send_message':
                // Display send message link if authenticated and not the identity owner
                return $this->_application->getUser()->isAuthenticated()
                    && $this->_application->getUser()->id != $this->_application->identity->id;
        }
    }

    public function systemRoutableOnMainTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/user/:user_id/messages':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('Inbox') : $title;
        }
    }

    /* End implementation of Plugg_System_Routable_Main */

    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/content/messages' => array(
                'controller' => 'Index',
                'title' => $this->_('Messages'),
                'type' => Plugg::ROUTE_TAB,
            ),
            '/content/messages/settings' => array(
                'controller' => 'Settings',
                'type' => Plugg::ROUTE_TAB,
                'title' => $this->_('Settings'),
                'title_callback' => true,
                'weight' => 30,
            ),
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {

        }
    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType)
    {
        switch ($path) {
            case '/content/messages/settings':
                return $titleType === Plugg::ROUTE_TITLE_TAB_DEFAULT ? $this->_('General') : $title;
        }
    }

    /* End implementation of Plugg_System_Routable_Admin */

    public function onPluggCron($lastrun, array $logs)
    {
        // Allow run this cron 1 time per day at most
        if (!empty($lastrun) && time() - $lastrun < 86400) return;

        if (!$delete_days = intval($this->getConfig('deleteOlderThanDays'))) return;
        
        $logs[] = sprintf($this->_('Deleting non-starred messages older than %d days.'), $delete_days);

        // Remove messages if no star and older than X days
        $criteria = $this->getModel()->createCriteria('Message')
            ->star_is(0)
            ->created_isSmallerThan($delete_days * 86400);

        $this->getModel()->getGateway('Message')->deleteByCriteria($criteria);
    }

    public function onUserIdentityDeleteSuccess($identity)
    {
        $id = $identity->id;
        $model = $this->getModel();

        // Remove stat data if any
        $model->getGateway('Message')
            ->deleteByCriteria($model->createCriteria('Message')->userId_is($id));
    }

    /* Start implementation of Plugg_User_Menu */

    public function userMenuGetNames()
    {
        return array('inbox');
    }

    public function userMenuGetNicename($menuName)
    {
        return $this->_('New messages');
    }

    public function userMenuGetLinkText($menuName, Sabai_User $user)
    {
        $count = $this->getModel()->Message
            ->criteria()
            ->type_is(self::MESSAGE_TYPE_INCOMING)
            ->read_is(0)
            ->countByUser($user->id);
        if ($count > 0) {
            return sprintf($this->_('Inbox (<strong>%d</strong>)'), $count);
        } else {
            return $this->_('Inbox');
        }
    }

    public function userMenuGetLinkUrl($menuName, Sabai_User $user)
    {
        return (string)$this->_application->User_IdentityUrl($user->getIdentity(), 'messages');
    }
    /* End implementation of Plugg_User_Menu */
    
    public function getDefaultConfig()
    {
        return array('deleteOlderThanDays' => 100);
    }
}
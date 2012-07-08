<?php
class Plugg_Messages_Controller_Main_User_Message extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Mark message as read if still unread
        if (!$this->message->isRead()) {
            $this->message->markRead();
            $this->message->commit();

            // Clear inbox user menu in session
            $this->getPlugin('User')->clearMenuInSession($this->getPlugin()->name, 'inbox');
        }
        
        $vars = array(
            'message' => $this->message,
            'message_from_to_user' => $this->getPlugin('User')->getIdentity($this->message->from_to, true),
        );

        $response->setContent($this->RenderTemplate('messages_main_user_message', $vars));
    }
}
<?php
class Plugg_Messages_Controller_Main_User_Message_Submit extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $url = array();

        if (!$request->isPost()) {
            $response->setError($this->_('Invalid request'), $url);
            return;
        }

        $actions_allowed = array('star', 'delete', 'read');
        if (!$action = $request->asStr('submit_action', false, $actions_allowed)) {
            foreach ($actions_allowed as $action_name) {
                if ($request->asStr('submit_action_' . $action_name)) {
                    $action = $action_name;
                    break;
                }
            }
            if (empty($action)) {
                $response->setError($this->_('Invalid request'), $url);
                return;
            }
        }

        if (!$token_value = $request->asStr(Plugg::PARAM_TOKEN, false)) {
            $response->setError($this->_('Invalid request'), $url);
            return;
        }
        if (!Sabai_Token::validate($token_value, 'messages_message_submit')) {
            $response->setError($this->_('Invalid request'), $url);
            return;
        }

        switch ($action) {
            case 'star':
                $this->message->markStarred(!$this->message->isStarred());
                break;
            case 'delete':
                $this->message->markRemoved();
                break;
            case 'read':
                $this->message->markRead(!$this->message->isRead());
                break;
        }

        if (!$this->message->commit()) {
            $response->setError($this->_('Message could not be updated.'), $url);
        } else {
            // Clear inbox user menu in session if read or delete action
            if (in_array($action, array('delete', 'read'))) {
                $this->getPlugin('User')->clearMenuInSession($this->getPlugin()->name, 'inbox');
            }
            $response->setSuccess($this->_('Message updated successfully.'), $url);
        }
    }
}
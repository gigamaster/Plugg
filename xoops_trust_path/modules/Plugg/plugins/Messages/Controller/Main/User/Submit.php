<?php
class Plugg_Messages_Controller_Main_User_Submit extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $messages_type = $request->asInt(
            'messages_type',
            Plugg_Messages_Plugin::MESSAGE_TYPE_INCOMING,
            array(Plugg_Messages_Plugin::MESSAGE_TYPE_INCOMING, Plugg_Messages_Plugin::MESSAGE_TYPE_OUTGOING)
        );
        $url = $this->User_IdentityUrl(
            $this->identity,
            $messages_type === Plugg_Messages_Plugin::MESSAGE_TYPE_OUTGOING ? 'messages/sent' : 'messages'
        );

        if (!$request->isPost()
            || (!$messages = $request->asArray('messages'))
            || (!$token_value = $request->asStr(Plugg::PARAM_TOKEN, false))
        ) {
            $this->setError($this->_('Invalid request'), $url);
            return;
        }
        if (!Sabai_Token::validate($token_value, 'messages_messages_submit')) {
            $this->setError($this->_('Invalid request'), $url);
            return;
        }

        if ($request->asStr('submit_delete')) {
            $action = 'delete';
        } else {
            $action = $request->asStr('submit_action');
            $actions_allowed = array('read', 'unread', 'star', 'unstar');
            if (!in_array($action, $actions_allowed)) {
                $this->setError($this->_('Invalid request'), $url);
                return;
            }
        }

        $model = $this->getPluginModel();
        $messages_current = $model->Message
            ->criteria()
            ->userId_is($this->identity->id)
            ->type_is($messages_type)
            ->id_in($messages)
            ->fetch();

        switch ($action) {
            case 'delete':
                foreach ($messages_current as $message) {
                    $message->markRemoved();
                }
                break;
            case 'read':
                foreach ($messages_current as $message) {
                    $message->read = 1;
                }
                break;
            case 'unread':
                foreach ($messages_current as $message) {
                    $message->read = 0;
                }
                break;
            case 'star':
                foreach ($messages_current as $message) {
                    $message->star = 1;
                }
                break;
            case 'unstar':
                foreach ($messages_current as $message) {
                    $message->star = 0;
                }
                break;
        }

        if (false === $num = $model->commit()) {
            $this->setError($this->_('An error occurred while updating messages.'), $url);
        } else {
            // Clear inbox user menu in session if read or delete action
            if (in_array($action, array('delete', 'read', 'unread'))) {
                $this->getPlugin('User')->clearMenuInSession($this->getPlugin()->name, 'inbox');
            }
            $response->setSuccess(sprintf($this->_('%d message(s) updated successfully.'), $num), $url);
        }
    }
}
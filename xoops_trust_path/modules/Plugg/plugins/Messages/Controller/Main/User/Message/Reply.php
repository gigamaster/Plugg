<?php
class Plugg_Messages_Controller_Main_User_Message_Reply extends Plugg_Form_Controller
{
    private $_fromUser;

    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_ajaxCancelType = 'slide';

        $this->_fromUser = $this->getPlugin('User')->getIdentity($this->message->from_to);

        $form = $this->getPluginModel()->createForm('Message');
        $form['to'] = array(
            '#type' => 'item',
            '#title' => $this->_('Send message to'),
            '#markup' => $this->User_IdentityThumbnail($this->_fromUser),
        );
        $form['title']['#default_value'] = !preg_match('/^Re: /i', $message_title = $this->message->title) ? 'Re: ' . $message_title : $message_title;
        $form['body']['#default_value'] = "\n\n" . strtr("\n" . $this->message->body, array("\n>" => "\n>>", "\n" => "\n> "));

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $reply = $this->getPluginModel()->create('Message');
        $reply->set($form->values);
        $reply->setIncoming();
        $reply->from_to = $this->getUser()->id;
        $reply->user_id = $this->_fromUser->id;

        // Create sent message
        $sent = clone $reply;
        $sent->setOutgoing();
        $sent->assignUser($this->getUser());
        $sent->from_to = $this->_fromUser->id;

        $reply->markNew();
        $sent->markNew();

        if (!$this->getPluginModel()->commit()) return false;

        $response->setSuccess($this->_('Message sent successfully.'));

        return true;
    }
}
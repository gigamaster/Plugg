<?php
class Plugg_Messages_Controller_Main_User_NewMessage extends Plugg_Form_Controller
{
    private $_toUser;

    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_ajaxCancelType = 'slide';

        // Init message
        $form = $this->getPluginModel()->createForm('Message');
        $form['to'] = array(
            '#type' => 'textfield',
            '#title' => $this->_('To'),
            '#size' => 30,
            '#maxlength' => 255,
            '#default_value' => $request->asStr('to'),
            '#required' => true,
            '#element_validate' => array(array($this, 'getUserByUsername')),
        );

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Create message
        $message = $this->getPluginModel()->create('Message');
        $message->set($form->values);
        $message->setIncoming();
        $message->assignUser($this->_toUser);
        $message->from_to = $this->getUser()->id;

        // Create sent message
        $sent = clone $message;
        $sent->setOutgoing();
        $sent->assignUser($this->getUser());
        $sent->from_to = $this->_toUser->id;

        $message->markNew();
        $sent->markNew();

        if (!$this->getPluginModel()->commit()) return false;

        $response->setSuccess(
            sprintf($this->_('Message sent to %s successfully.'), $this->_toUser->display_name)
        );

        return true;
    }

    /**
     * This method must be public to be as used as form validation callback
     */
    public function getUserByUsername($form, $value, $name)
    {
        $this->_toUser = $this->getPlugin('User')->getIdentityFetcher()
            ->fetchUserIdentityByUsername($value);

        return !$this->_toUser->isAnonymous();
    }
}
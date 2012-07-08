<?php
class Plugg_User_Controller_Admin_Queues_SendQueue extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $url = array('base' => '/user/queues');

        // Check if user account plugin is valid
        if ($this->getPlugin()->getManagerPlugin() instanceof Plugg_User_Manager_API) {
            $response->setError($this->_('Invalid request'), $url);

            return;
        }

        switch ($this->queue->type) {
            case Plugg_User_Plugin::QUEUE_TYPE_REGISTER:
            case Plugg_User_Plugin::QUEUE_TYPE_REGISTERAUTH:
                $result = $this->getPlugin()->sendRegisterConfirmEmail($this->queue);
                break;
            case Plugg_User_Plugin::QUEUE_TYPE_REQUESTPASSWORD:
                $result = $this->getPlugin()->sendRequestPasswordConfirmEmail($this->queue);
                break;
            case Plugg_User_Plugin::QUEUE_TYPE_EDITEMAIL:
                $result = $this->getPlugin()->sendEditEmailConfirmEmail($this->queue);
                break;
            default:
                $response->setError($this->_('Invalid request'), $url);
                return;
        }

        if ($result) {
            $response->setSuccess($this->_('Confirmation mail sent successfully.'), $url);
        } else {
            $response->setError($this->_('An error occurred while sending confirmation mail.'), $url);
        }
    }
}
<?php
class Plugg_Friends_Controller_Main_User_SendRequest extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        // Check if authenticated
        if (!$this->getUser()->isAuthenticated()) {
            $response->setLoginRequiredError();

            return false;
        }

        $model = $this->getPluginModel();

        // Check if already friends
        $count = $model->Friend
            ->criteria()
            ->with_is($this->identity->id)
            ->countByUser($this->getUser()->id);
        if ($count > 0) {
            $response->setError($this->_('You have already added the user as a friend.'));

            return false;
        }

        // Check if there is a pending request from the user
        $count = $model->Request
            ->criteria()
            ->status_is(Plugg_Friends_Plugin::REQUEST_STATUS_PENDING)
            ->to_is($this->getUser()->id)
            ->countByUser($this->identity->id);
        if ($count > 0) {
            $response->setError(
                $this->_('There is a pending friend request sent to you from this user.')
            );

            return false;
        }

        // Check if request was submitted recently
        $count = $model->Request
            ->criteria()
            ->to_is($this->identity->id)
            ->countByUser($this->getUser()->id);
        if ($count > 0) {
            $response->setError(
                $this->_('You have recently sent a friend request to this user.')
            );

            return false;
        }

        $this->_cancelUrl = array();
        
        return $model->createForm('Request');
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $model = $this->getPluginModel();

        // Check if the target user already has the current user as a friend.
        // This can happen when target user had been removed from the list previously.
        $count = $model->Friend
            ->criteria()
            ->with_is($this->getUser()->id)
            ->countByUser($this->identity->id);
        if ($count > 0) {
            // The target user already has the current user as a friend, so add the target user
            // as a friend without sending a new friend request.
            $friend = $model->create('Friend');
            $friend->user_id = $this->getUser()->id;
            $friend->with = $this->identity->id;
            $friend->relationships = 'contact'; // Defaults to "contact" relationship
            if ($friend->markNew()->commit()) {
                $response->setSuccess($this->_('You are now friends.'));

                return true;
            }

            return false;
        }

        $request = $model->create('Request');
        $request->set($form->values);
        $request->to = $this->identity->id;
        $request->assignUser($this->getUser());
        $request->setPending();
        $request->markNew();
        if ($request->commit()) {
            $message = sprintf(
                $this->_('Your friend request to %s submitted successfully.'),
                $this->identity->display_name
            );
            $response->setSuccess($message);

            return true;
        }

        return false;
    }
}
<?php
class Plugg_User_Controller_Main_Identity_Settings_Delete extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_submitButtonLabel = $this->_('Yes, delete');

        $form['#header'][] = sprintf(
            '<div class="plugg-warning">%s</div>',
            $this->_('Are you sure you want to delete this user account?')
        );
        $form['User'] = array(
            '#type' => 'item',
            '#title' => $this->_('User'),
            '#markup' => $this->User_IdentityThumbnail($this->identity),
        );

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!$this->getPlugin()->getManagerPlugin()->userDeleteSubmit($this->identity)) return false;

        $response->setSuccess($this->_('User data removed successfully'));
        $this->DispatchEvent('UserIdentityDeleteSuccess', array($this->identity));

        return true;
    }
}
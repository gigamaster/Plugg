<?php
class Plugg_User_Controller_Main_Identity_Edit_Image extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_cancelUrl = array();

        $form = $this->getPlugin()->getManagerPlugin()->userEditImageGetForm($this->identity);
        $form['#action'] = $this->getUrl('/user/' . $this->identity->id . '/edit/image');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($this->getPlugin()->getManagerPlugin()->userEditImageSubmitForm($this->identity, $form)) {
            $this->DispatchEvent('UserIdentityEditImageSuccess', array($this->identity));

            return true;
        }

        return false;
    }
}
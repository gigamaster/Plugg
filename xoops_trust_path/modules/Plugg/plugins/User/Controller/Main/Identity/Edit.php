<?php
class Plugg_User_Controller_Main_Identity_Edit extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_cancelUrl = array();

        $form = $this->getPlugin()->getManagerPlugin()->userEditGetForm($this->identity);
        $form['#action'] = $this->createUrl(array('path' => 'edit'));

        // Add extra form field settings
        if (!$this->identity->hasData('user_fields')
            || (!$extra_fields = $this->identity->getData('user_fields'))
        ) {
            $extra_fields = array();
        }
        if (!$this->identity->hasData('user_fields_visibility')
            || (!$extra_fields_visibility = $this->identity->getData('user_fields_visibility'))
        ) {
            $extra_fields_visibility = array();
        }
        $this->getPlugin()->addExtraFormFields($form, $this->identity, $extra_fields, $extra_fields_visibility);

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($identity = $this->getPlugin()->getManagerPlugin()->userEditSubmitForm($this->identity, $form)) {
            $extra_fields = $this->getPlugin()->extractExtraFormFieldValues($form->values);
            $this->User_IdentitySaveMeta($identity, array(
                'user_fields' => $extra_fields['value'],
                'user_fields_visibility' => $extra_fields['visibility']
            ));
            $this->DispatchEvent('UserIdentityEditSuccess', array($identity));

            return true;
        }

        return false;
    }
}
<?php
class Plugg_User_Controller_Main_Identity_Edit_Status extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if ($request->isAjax()) $response->setFlashEnabled(false);
        
        $this->_cancelUrl = array();
        $this->_ajaxCancelType = 'remote';
        $this->_ajaxCancelUrl = $this->_ajaxOnSuccessUrl = $this->getUrl('/user/' . $this->identity->id . '/status');

        $model = $this->getPluginModel();
        if (!$this->status = $model->Status->fetchByUser($this->identity->id)->getFirst()) {
            $this->status = $model->create('Status');
            $this->status->markNew();
            $this->status->assignUser($this->identity);
        }

        return $model->createForm($this->status);
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->status->text = $form->values['text']['text'];
        $this->status->text_filtered = $form->values['text']['filtered_text'];
        $this->status->text_filter_id = $form->values['text']['filter_id'];

        if ($this->status->commit()) {
            $this->DispatchEvent('UserIdentityEditStatusSuccess', array($this->identity, $this->status));

            return true;
        }

        return false;
    }
}
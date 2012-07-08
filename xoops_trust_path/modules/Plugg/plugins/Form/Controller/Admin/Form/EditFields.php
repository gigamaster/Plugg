<?php
class Plugg_Form_Controller_Admin_Form_EditFields extends Sabai_Application_Controller
{
    function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (false === $formfields = $this->getPlugin()->getFormFields($this->form)) {
            $response->setError($this->_('An error occurred.'));
            return;
        }
        
        $vars = array(
            'fields' => $formfields,     
            'field_data' => $this->getPlugin()->getFieldData(),
            'form_id' => $form_id = 'plugg-form-form-edit-fields',
            'form_submit_path' => '/content/form/' . $this->form->id . '/submit/fields',
            'form_submit_token_id' => 'form_admin_form_submit',
        );

        // Add js/css for managing widgets
        $js = $this->getPlugin()->getAdminFieldsJS(
            $form_id,
            $this->getUrl('/content/form/' . $this->form->id . '/edit/field'),
            $this->getUrl('/content/form/' . $this->form->id . '/edit/fieldset')
        );
        $response->setContent($this->RenderTemplate('form_admin_form_editfields', $vars))
            ->addJs($js)
            ->addCss($this->getPlugin()->getAdminFieldsCSS());
    }
}
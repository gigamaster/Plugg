<?php
class Plugg_User_Controller_Admin_Fields extends Sabai_Application_Controller
{
    function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (false === $fields = $this->getPlugin()->getFields()) {
            $response->setError($this->_('An error occurred.'));
            return;
        }
        
        $vars = array(
            'fields' => $fields,
            'field_data' => $this->getPlugin('Form')->getFieldData(),
            'form_id' => 'plugg-user-form-fields',
            'form_submit_path' => '/user/fields/submit',
            'form_submit_token_id' => 'user_admin_fields_submit'
        );

        // Add js/css for managing widgets
        $js = $this->getPlugin('Form')->getAdminFieldsJS(
            'plugg-user-form-fields',
            $this->getUrl('/user/fields/edit/field'),
            $this->getUrl('/user/fields/edit/fieldset')
        );
        $response->setContent($this->RenderTemplate('user_admin_fields', $vars))
            ->addJs($js)
            ->addCss($this->getPlugin('Form')->getAdminFieldsCSS());
    }
}
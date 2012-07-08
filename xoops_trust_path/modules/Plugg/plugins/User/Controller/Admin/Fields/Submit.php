<?php
class Plugg_User_Controller_Admin_Fields_Submit extends Sabai_Application_Controller
{
    function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $url = $this->getUrl('/user/fields');

        // Check request token
        if (!$request->isPost()
            || (!$token_value = $request->asStr(Plugg::PARAM_TOKEN, false))
            || !Sabai_Token::validate($token_value, 'user_admin_fields_submit', false)
        ) {
            $response->setError($this->_('Invalid request'), $url);
            return;
        }

        // Fetch current fields
        $current_fields = array();
        foreach ($this->getPluginModel()->Field->fetch() as $current_field) {
            $current_fields[$current_field->id] = $current_field;
        }

        if ($fields = $request->asArray('fields')) {
            $field_data = $this->getPlugin('Form')->getFieldData();
            $fieldset_field_id = $this->getPlugin('Form')->getFieldsetField()->id;
            $fieldset_id = 0;
            foreach ($fields as $field_weight => $field_id) {
                if (!isset($current_fields[$field_id])) continue;
                
                $field = $current_fields[$field_id];  
                // Is it a fieldset?
                if ($field->field_id == $fieldset_field_id) {
                    $fieldset_id = $field_id;
                } else {                
                    // Make sure that the field type exists
                    if (!isset($field_data[$field->field_id])) continue;
                    
                    $field->fieldset = $fieldset_id;
                }
                             
                unset($current_fields[$field_id]);
                $field->weight = $field_weight;
            }
        }

        // Remove current form fields that were not included in the request
        foreach ($current_fields as $current_field) {
            $current_field->markRemoved();
        }

        // Commit all changes
        if (false === $this->getPluginModel()->commit()) {
            $response->setError($this->_('An error occurred while updating data.'), $url);
        } else {
            $response->setSuccess($this->_('Data updated successfully.'), $url);
        }
        if ($request->isAjax()) $response->setFlashEnabled(false);
    }
}
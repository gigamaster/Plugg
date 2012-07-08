<?php
class Plugg_Form_Controller_Admin_Form_SubmitFields extends Sabai_Application_Controller
{
    function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $url = $this->getUrl('/content/form/' . $this->form->id . '/edit/fields');

        // Check request token
        if (!$request->isPost()
            || (!$token_value = $request->asStr(Plugg::PARAM_TOKEN, false))
            || !Sabai_Token::validate($token_value, 'form_admin_form_submit', false)
        ) {
            $response->setError($this->_('Invalid request'), $url);
            return;
        }

        // Fetch current form fields
        $model = $this->getPluginModel();
        $current_formfields = array();
        foreach ($model->Formfield->fetchByForm($this->form->id) as $current_formfield) {
            $current_formfields[$current_formfield->id] = $current_formfield;
        }

        // Create field records if any
        if ($formfields = $request->asArray('fields')) {
            $field_data = $this->getPlugin()->getFieldData();
            $fieldset_field_id = $this->getPlugin()->getFieldsetField()->id;
            $fieldset_id = 0;
            foreach ($formfields as $formfield_weight => $formfield_id) {
                if (!isset($current_formfields[$formfield_id])) continue;
                
                $formfield = $current_formfields[$formfield_id];  
                // Is it a fieldset?
                if ($formfield->field_id == $fieldset_field_id) {
                    $fieldset_id = $formfield_id;
                } else {                
                    // Make sure that the field type exists
                    if (!isset($field_data[$formfield->field_id])) continue;
                    
                    $formfield->fieldset = $fieldset_id;
                }
                             
                unset($current_formfields[$formfield_id]);
                $formfield->weight = $formfield_weight;
            }
        }

        // Remove current form fields that were not included in the request
        foreach ($current_formfields as $current_formfield) {
            $current_formfield->markRemoved();
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
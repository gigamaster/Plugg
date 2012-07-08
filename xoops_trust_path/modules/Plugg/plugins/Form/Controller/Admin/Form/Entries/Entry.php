<?php
class Plugg_Form_Controller_Admin_Form_Entries_Entry extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $data = unserialize($this->formentry->data);

        $form['#ignore_default_value'] = true;
        $form['fields'] = array('#tree' => true);
        $formfields = $this->getPlugin()->getFormFields($this->form);
        if (!empty($formfields[0])) {
            foreach ($formfields[0] as $fieldset) {
                $field_settings = array();
                foreach (unserialize($fieldset->settings) as $field_setting_key => $field_setting) {
                    $field_settings['#' . $field_setting_key] = $field_setting;
                }
                $form['fields'][$fieldset->name] = array_merge(
                    $field_settings,
                    array(
                        '#type' => 'fieldset',
                        '#title' => $fieldset->title,
                        '#description' => $fieldset->description,
                        '#weight' => $fieldset->weight,
                    )
                );
                if (!empty($formfields[$fieldset->id])) {
                    foreach ($formfields[$fieldset->id] as $field) {
                        $field_settings = array();
                        foreach (unserialize($field->settings) as $field_setting_key => $field_setting) {
                            $field_settings['#' . $field_setting_key] = $field_setting;
                        }
                        $form['fields'][$fieldset->name][$field->name] = array_merge(
                            $field_settings,
                            array(
                                '#type' => $field->Field->type,
                                '#title' => $field->title,
                                '#description' => $field->description,
                                '#weight' => $field->weight,
                                '#required' => $field->required,
                                '#disabled' => $field->disabled,
                                '#value' => isset($data[$fieldset->name][$field->name]) ? $data[$fieldset->name][$field->name] : null,
                            )
                        );
                    }
                }
            }
        }

        $response->setContent($this->getPlugin()->renderForm($form, false, true));
    }
}
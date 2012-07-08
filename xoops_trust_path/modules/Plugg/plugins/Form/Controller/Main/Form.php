<?php
class Plugg_Form_Controller_Main_Form extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_cancelUrl = null;
        
        if ($this->getUser()->isAuthenticated()) {
            if (!$this->getUser()->hasPermission('Form view form ' . $this->form->id)) return false;
        
            $this->_submitable = $this->getUser()->hasPermission('Form submit form ' . $this->form->id);
        } else {
            // Anonymous user
            if (!$this->form->anon_view) return false;
            
            $this->_submitable = $this->form->anon_submit;
        }
        
        // Confirm before submit?
        if ($this->form->confirm) {
            if ($this->form->confirm_button_label
                && ($confirm_button_label = $this->Trim($this->form->confirm_button_label))
            ) {
                $this->_submitButtonLabel = $confirm_button_label;
            } else {
                $this->_submitButtonLabel = $this->_('Confirm');
            }
        } else {
            $this->_submitButtonLabel = $this->_getSubmitButtonLabel($this->form);
        }
        
        $form['#header'][] = $this->form->header;
        $form['fields'] = array('#tree' => true, '#type' => 'fieldset');
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
                        '#collapsible' => $fieldset->collapsible,
                        '#collapsed' => $fieldset->collapsed,
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
                            )
                        );
                    }
                }
            }
        }
        
        $form['#submit'][] = array(array($this, 'returnForm'), array($request));
        
        $response->setPageTitle($this->form->title);
        
        return $form;
    }
    
    public function returnForm(Plugg_Form_Form $form, Sabai_Request $request)
    {
        if ($this->form->confirm
            && !empty($form->values['confirmed'])
            && !$form->getClickedButton()
        ) {
            // The form was confirmed, but the submit buttons was not clicked, meaning
            // that the Return button has been clicked, so return false to display the form.
            return false;
        }
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($this->form->confirm && empty($form->values['confirmed'])) {
            $this->_freeze = true;
            
            // Prevent default values from being assigned to the form, so that
            // the values for fields like checkboxes will not get overriden by
            // the default values when the user did not select any of the options. 
            $form->settings['#ignore_default_value'] = true;

            $form->settings['confirmed'] = array(
                '#type' => 'hidden',
                '#value' => 1,
            );
            
            $form->settings[$this->_submitButtonName]['submit']['#value'] = $this->_getSubmitButtonLabel($this->form);
            $form->settings[$this->_submitButtonName]['return'] = array(
                '#type' => 'submit',
                '#value' => $this->_('Return'),
                '#weight' => -1,
            );
            
            // Rebuild form to reflect changes made to the settings
            $form->rebuild = true;
            
            return false; // display form
        }

        $form_entry = $this->form->createFormentry();
        $form_entry->data = serialize($form->values['fields']);
        $form_entry->assignUser($this->getUser());
        $form_entry->ip = getip();
        $form_entry->markNew();
        
        if (!$form_entry->commit()) return false;
        
        $tags = $this->getPlugin()->getEmailTags($this->form, $form_entry);
        $email_settings = $this->form->getEmailSettings();
        if (!empty($email_settings['confirmation'])) $this->_sendConfirmationEmail($email_settings['confirmation'], $tags);
        if (!empty($email_settings['notification'])) $this->_sendNotificationEmail($email_settings['notification'], $tags);
        
        $response->setSuccess($this->_('Form has been submitted successfully.'));
        
        return true;
    }
    
    private function _getSubmitButtonLabel($form)
    {
        // Any custom submit button label defined?
        if ($form->submit_button_label
            && ($submit_button_label = $this->Trim($form->submit_button_label))
        ) {
            return $submit_button_label;
        }
        
        return $this->_('Submit');
    }
    
    private function _sendConfirmationEmail($settings, $tags)
    {
        if ($this->getUser()->isAuthenticated() && !empty($settings['to']['user'])) {
            $to = $this->getUser()->email;        
        }
        
        if (empty($to)) return;

        $this->getPlugin('Mail')->getSender()->mailSend(
            array($this->getUser()->email, $this->getUser()->display_name),
            strtr($settings['subject'], $tags),
            strtr($settings['body'], $tags)
        );
    }
    
    private function _sendNotificationEmail($settings, $tags)
    {
        if (empty($settings['to'])) return;

        $subject = strtr($settings['subject'], $tags);
        $body = strtr($settings['body'], $tags);
        foreach ($settings['to'] as $to) {
            $this->getPlugin('Mail')->getSender()->mailSend($to, $subject, $body);
        }
    }
}
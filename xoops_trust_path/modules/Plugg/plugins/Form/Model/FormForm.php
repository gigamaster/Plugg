<?php
class Plugg_Form_Model_FormForm extends Plugg_Form_Model_Base_FormForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);
        
        $settings['header']['#description'] = $this->_model->_('A short description of the form displayed at the top of the form.');
        $settings['header']['#type'] = 'filters_textarea';
        $settings['header']['#default_value'] = array(
            'text' => $entity->header,
            'filter_id' => $entity->header_format,
        );
        
        $settings['weight']['#description'] = $this->_model->_('Lighter forms will be listed above the heavier forms on the top page.');
        $settings['weight']['#size'] = 5;
        $settings['hidden']['#description'] = $this->_model->_('Check this option to disable all user access to the form.');
        $settings['submit_button_label']['#title'] = $this->_model->_('Submit button label');
        $settings['submit_button_label']['#description'] = $this->_model->_('By default the submit button on the form will have the label <b><i>Submit</i></b>. Enter a new title here to override the default.');
        $settings['submit_button_label']['#size'] = 15;
        $settings['confirm']['#title'] = $this->_model->_('Force the user to confirm form values before submit');
        $settings['confirm']['#description'] = $this->_model->_('Check this option to force the user to preview form values before actually being submitted.');
        $settings['confirm_button_label']['#title'] = $this->_model->_('Confirm button label');
        $settings['confirm_button_label']['#description'] = $this->_model->_('By default the confirm button on the form will have the label <b><i>Confirm</i></b>. Enter a new title here to override the default.');
        $settings['confirm_button_label']['#size'] = 15;
        $settings['display'] = array(
            '#type' => 'fieldset',
            '#title' => $this->_model->_('Display options'),
            '#weight' => 10,
            '#collapsible' => true,
            '#collapsed' => true,
            '#rows' => 2,
        );
        foreach (array('weight', 'hidden', 'submit_button_label', 'confirm', 'confirm_button_label') as $k) {
            $settings['display'][$k] = $settings[$k];
            unset($settings[$k]);
        }
        
        // Add access control settings field
        if ($entity->id) {
            $access_view = $access_submit = array();
            if ($entity->anon_view) $access_view[] = 0;
            if ($entity->anon_submit) $access_submit[] = 0;
        }
        $role_options = array(0 => $this->_model->_('Anonymous users'));
        foreach ($this->_model->User_Roles() as $role) {
            $role_options[$role->id] = sprintf($this->_model->_('Users with the "%s" role'), $role->display_name);
            if ($entity->id) {
                if ($role->hasPermission('Form', 'Form view form ' . $entity->id)) $access_view[] = $role->id;
                if ($role->hasPermission('Form', 'Form submit form ' . $entity->id)) $access_submit[] = $role->id;
            }
        }
        $settings['access'] = array(
            '#title' => $this->_model->_('Access control'),
            '#weight' => 5,
            '#collapsible' => true,
            '#collapsed' => true,
            '#tree' => true,
            'view' => array(
                '#type' => 'checkboxes',
                '#title' => $this->_model->_('Users that can view this form.'),
                '#options' => $role_options,
                '#default_value' => isset($access_view) ? $access_view : array_keys($role_options),
            ),
            'submit' => array(
                '#type' => 'checkboxes',
                '#title' => $this->_model->_('Users that can submit this form.'),
                '#options' => $role_options,
                '#default_value' => isset($access_submit) ? $access_submit : array_keys($role_options),
            ),
        );

        return $settings;
    }
}
<?php
class Plugg_User_Model_FieldForm extends Plugg_User_Model_Base_FieldForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);
        $settings['description']['#description'] = $this->_model->_('A short description of the field.');
        $settings['description']['#rows'] = 3;
        
        $settings['weight']['#size'] = 5;
        
        $settings['name']['#title'] = $this->_model->_('Field key');
        $settings['name']['#regex'] = '/^[a-z0-9_]+$/';
        $settings['name']['#size'] = 20;
        $settings['name']['#element_validate'][] = array(array($this, 'validateFieldName'), array($entity)); 
        
        $settings['required']['#description'] = $this->_model->_('Check this option to make this field mandatory.');
        
        $settings['collapsible']['#default_value'] = $entity->id ? $entity->collapsible : true;
        $settings['collapsed']['#title'] = $this->_model->_('Collapsed by default');
        
        $settings['registerable']['#title'] = $this->_model->_('Display on user registration page.');
        
        $settings['editable']['#title'] = $this->_model->_('Allow the user to edit this field.');
        $settings['editable']['#default_value'] = $entity->id ? $entity->editable : true;
        
        $settings['visibility_default']['#title'] = $this->_model->_('Default field visibility');
        $settings['visibility_default']['#description'] = $this->_model->_('Select which type of users can view this field on the user profile page.');
        $settings['visibility_default']['#options'] = $this->_model->User_FieldVisibilities(true);
        $settings['visibility_default']['#default_value'] = $entity->id ? $entity->visibility_default : array('@all');
        
        $settings['visibility_control']['#title'] = $this->_model->_('Allow the user to change field visibility.');
        $settings['visibility_control']['#default_value'] = $entity->id ? $entity->visibility_control : true;
        $settings['visibility_control']['#weight'] = 19;

        return $settings;
    }
    
    public function validateFieldName(Plugg_Form_Form $form, $value, $name, $field)
    {   
        // Make sure the form key is not in use by other fields
        
        $repository = $this->_model->Field->criteria()->name_is($value);
        
        // Skip counting self
        if ($field->id) $repository->id_isNot($field->id);
        
        // If the field is a fieldset, only check against other fieldsets
        $fieldset_field_id = $this->_model->Form_FieldsetField()->id;
        if ($field->field_id == $fieldset_field_id) {
            if ($repository->fieldId_is($field->field_id)->count()) {
                $form->setError($this->_model->_('The field key is already in use by another fieldset.'), $name);
            }
        } else {
            // Exclude fiedsets
            if ($repository->fieldId_isNot($fieldset_field_id)->count()) {
                $form->setError($this->_model->_('The field key is already in use by another field.'), $name);
            } 
        }
    }
}
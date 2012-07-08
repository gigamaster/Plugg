<?php
class Plugg_Form_Model_FormfieldForm extends Plugg_Form_Model_Base_FormfieldForm
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
        
        $settings['disabled']['#description'] = $this->_model->_('Make this field non-editable. Useful for setting an unchangeable default value.');
        
        $settings['collapsed']['#title'] = $this->_model->_('Collapsed by default');
        $settings['collapsible']['#default_value'] = $entity->id ? $entity->collapsible : true;

        return $settings;
    }
    
    public function validateFieldName(Plugg_Form_Form $form, $value, $name, $formfield)
    {   
        // Make sure the form key is not in use by other fields
        
        $repository = $this->_model->Formfield->criteria()->name_is($value)->formId_is($formfield->form_id);
        
        // Skip counting self
        if ($formfield->id) $repository->id_isNot($formfield->id);
        
        // If the field is a fieldset, only check against other fieldsets
        $fieldset_field_id = $this->_model->Form_FieldsetField()->id;
        if ($formfield->field_id == $fieldset_field_id) {
            if ($repository->fieldId_is($formfield->field_id)->count()) {
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
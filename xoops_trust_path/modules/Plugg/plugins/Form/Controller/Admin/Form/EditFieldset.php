<?php
class Plugg_Form_Controller_Admin_Form_EditFieldset extends Plugg_Form_Controller
{
    private $_formfield;
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $fieldset_field = $this->getPlugin()->getFieldsetField();
        
        if (!$fieldset_field) return false; // this should never happen but just in case
        
        $this->_formfield = $this->_getFormfield($request, $fieldset_field);

        // Define form
        $form = $this->getPluginModel()->createForm($this->_formfield);
        $form['#action'] = $this->getUrl('/content/form/' . $this->form->id . '/edit/fieldset');
        $form['#token'] = array('reuse' => true);

        // Generate settings form
        $current_settings = $this->_formfield->settings ? unserialize($this->_formfield->settings) : array();
        $form['settings'] = array_merge(
            $this->getPlugin()->formFieldGetSettings('fieldset', $current_settings),
            array(
                '#type' => 'fieldset',
                '#tree' => true,
                '#tree_allow_override' => false,
                '#weight' => 50,
            )
        );
        $form['description']['#description'] = $this->_('A short description of the fieldset.');
        $form['name']['#description'] = $this->_('Enter a machine readable key for this form field. Only lowercase alphanumeric characters and underscores are allowed.');
        $form['collapsible']['#description'] = $this->_('If this fieldset is collapsible, the user may open or close the fieldset. Note that the label of this fieldset must not be empty for this feature to work properly.');
        $form['collapsed']['#description'] = $this->_('Collapsible fieldsets are expanded by default. Check this option to set the fieldset collapsed by default.');
        $form['field_id'] = array(
            '#type' => 'hidden',
            '#value' => $fieldset_field->id,
        );
        if ($this->_formfield->id) {
            $form['name']['#collapsible'] = true;
            $form['name']['#collapsed'] = true;
            $form['formfield_id'] = array(
                '#type' => 'hidden',
                '#value' => $this->_formfield->id,
            );
            // The field key of default fieldset may not be modified
            if ($this->_formfield->name == 'default') {
                $form['name']['#disabled'] = true;
            }
        }
        unset($form['weight'], $form['required'], $form['disabled']);
        
        // Do not require label
        $form['title']['#required'] = false;
        
        // Set options
        $this->_submitButtonLabel = $this->_('Save configuration');
        $this->_ajaxOnSuccessRedirect = false;
        $this->_ajaxCancelType = $this->_formfield->id ? 'slide' : 'none';
        $this->_ajaxOnSuccess = 'function(xhr, result, target) {
            var $fieldset = target.slideUp("fast").closest(".form-fields-fieldset");
            if ($fieldset.hasClass("form-fields-fieldset-new")) {
                $fieldset.removeClass("form-fields-fieldset-new").addClass("form-fields-fieldset-new-saved");
            }
            $fieldset.find(".form-fields-fieldset-id").attr("value", result.formfield_id);
            $fieldset.find(".form-fields-fieldset-label span").html(result.formfield_label);
        }';

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($this->_formfield->name !== 'default') $this->_formfield->name = $form->values['name'];
        $this->_formfield->title = $form->values['title'];
        $this->_formfield->description = $form->values['description'];
        $this->_formfield->settings = serialize(isset($form->values['settings']) ? $form->values['settings'] : array());
        $this->_formfield->collapsible = $form->values['collapsible'];
        $this->_formfield->collapsed = $form->values['collapsed'];

        if (!$this->_formfield->commit()) return false;
        
        $response->setSuccess(
            $this->_('Fieldset saved successfully.'),
            $this->getUrl('/content/form/' . $this->form->id . '/edit/fields'),
            array(
                'formfield_id' => $this->_formfield->id,
                'formfield_label' => $this->_formfield->title ? h(mb_strimlength($this->_formfield->title, 0, 30)) : '&nbsp;'
            )
        );
    }

    private function _getFormfield(Sabai_Request $request, $fieldsetField)
    {
        if (($formfield_id = $request->asInt('formfield_id'))
            && ($formfield = $this->getPluginModel()->Formfield->fetchById($formfield_id))
            && $formfield->form_id == $this->form->id
            && $formfield->field_id == $fieldsetField->id
        ) {
            return $formfield;
        }

        $formfield = $this->form->createFormfield();
        $formfield->assignField($fieldsetField);
        $formfield->markNew();

        return $formfield;
    }
}
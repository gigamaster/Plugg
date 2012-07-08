<?php
class Plugg_Form_Controller_Admin_Form_EditField extends Plugg_Form_Controller
{
    private $_formfield;
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$field_plugin = $this->getPlugin($this->field->plugin)) return false;

        $this->_formfield = $this->_getFormfield($request);

        // Define form
        $form = $this->getPluginModel()->createForm($this->_formfield);
        $form['#id'] = sprintf('%s_%s_%s', 'form_admin_form_editfield', $this->field->plugin, $this->field->type);
        $form['#action'] = $this->getUrl('/content/form/' . $this->form->id . '/edit/field');
        $form['#token'] = array('reuse' => true);
        $form['name']['#description'] = $this->_('Enter a machine readable key for this form field. This value will be used as the name attribute of the form field. Only lowercase alphanumeric characters and underscores are allowed.');

        // Generate settings form
        $current_settings = $this->_formfield->settings ? unserialize($this->_formfield->settings) : array();
        $form['settings'] = array_merge(
            $field_plugin->formFieldGetSettings($this->field->type, $current_settings),
            array(
                '#type' => 'fieldset',
                '#tree' => true,
                '#tree_allow_override' => false,
                '#weight' => 50,
            )
        );
        
        $form['field_id'] = array(
            '#type' => 'hidden',
            '#value' => $this->field->id,
        );
        if ($this->_formfield->id) {
            $form['name']['#collapsible'] = true;
            $form['name']['#collapsed'] = true;
            $form['type'] = array(
                '#type' => 'item',
                '#title' => $this->_('Type'),
                '#markup' => h($field_plugin->formFieldGetTitle($this->field->type)),
                '#weight' => 1, // Display at the top
            );
            $form['formfield_id'] = array(
                '#type' => 'hidden',
                '#value' => $this->_formfield->id,
            );
        }
        unset($form['weight'], $form['collapsible'], $form['collapsed']);
        
        // Set options
        $this->_submitButtonLabel = $this->_('Save configuration');
        $this->_ajaxOnSuccessRedirect = false;
        $this->_ajaxOnErrorRedirect = false;
        $this->_ajaxCancelType = $this->_formfield->id ? 'slide' : 'none';
        $this->_ajaxOnSuccess = 'function(xhr, result, target) {
            var $field = target.slideUp("fast").closest(".form-fields-field");
            if ($field.hasClass("form-fields-field-new")) {
                $field.removeClass("form-fields-field-new").addClass("form-fields-field-new-saved");
            }
            $field.find(".form-fields-field-id").attr("value", result.formfield_id);
            $field.find(".form-fields-field-label span").html(result.formfield_label);
        }';
        $this->_ajaxOnError = 'function(xhr, result, target) {
            target.slideUp("fast");
            alert(result.message);
        }';

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->_formfield->name = $form->values['name'];
        $this->_formfield->title = $form->values['title'];
        $this->_formfield->description = $form->values['description'];
        $this->_formfield->required = $form->values['required'];
        $this->_formfield->disabled = $form->values['disabled'];
        $this->_formfield->settings = serialize(isset($form->values['settings']) ? $form->values['settings'] : array());

        if (!$this->_formfield->commit()) {
            $response->setError(
                $this->_('An error occurred.'),
                $this->getUrl('/content/form/' . $this->form->id . '/edit/fields')
            );
            
            return;
        }
        
        $response->setSuccess(
            $this->_('Field saved successfully.'),
            $this->getUrl('/content/form/' . $this->form->id . '/edit/fields'),
            array(
                'formfield_id' => $this->_formfield->id,
                'formfield_label' => h(mb_strimlength($this->_formfield->title, 0, 30))
            )
        );
    }

    private function _getFormfield($request)
    {
        if (($formfield_id = $request->asInt('formfield_id'))
            && ($formfield = $this->getPluginModel()->Formfield->fetchById($formfield_id))
            && $formfield->form_id == $this->form->id
            && $formfield->field_id == $this->field->id
        ) {
            return $formfield;
        }
        
        $formfield = $this->form->createFormfield();
        $formfield->assignField($this->field);
        $formfield->markNew();

        return $formfield;
    }
}
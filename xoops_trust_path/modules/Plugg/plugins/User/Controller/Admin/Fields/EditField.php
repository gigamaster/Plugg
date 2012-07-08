<?php
class Plugg_User_Controller_Admin_Fields_EditField extends Plugg_Form_Controller
{
    private $_userfield;
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$field_plugin = $this->getPlugin($this->field->plugin)) return false;

        $this->_userfield = $this->_getUserField($request);

        // Define form
        $form = $this->getPluginModel()->createForm($this->_userfield);
        $form['#id'] = sprintf('%s_%s_%s', 'user_admin_fields_editfield', $this->field->plugin, $this->field->type);
        $form['#action'] = $this->getUrl('/user/fields/edit/field');
        $form['#token'] = array('reuse' => true);
        $form['name']['#description'] = $this->_('Enter a machine readable key for this form field. This value will be used as the name attribute of the form field. Only lowercase alphanumeric characters and underscores are allowed.');

        // Generate settings form
        $current_settings = $this->_userfield->settings ? unserialize($this->_userfield->settings) : array();
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
        if ($this->_userfield->id) {
            $form['name']['#collapsible'] = true;
            $form['name']['#collapsed'] = true;
            $form['type'] = array(
                '#type' => 'item',
                '#title' => $this->_('Type'),
                '#markup' => h($field_plugin->formFieldGetTitle($this->field->type)),
                '#weight' => 1, // Display at the top
            );
            $form['userfield_id'] = array(
                '#type' => 'hidden',
                '#value' => $this->_userfield->id,
            );
        }
        unset($form['weight'], $form['collapsible'], $form['collapsed']);
        
        // Set options
        $this->_submitButtonLabel = $this->_('Save configuration');
        $this->_ajaxOnSuccessRedirect = false;
        $this->_ajaxOnErrorRedirect = false;
        $this->_ajaxCancelType = $this->_userfield->id ? 'slide' : 'none';
        $this->_ajaxOnSuccess = 'function(xhr, result, target) {
            var $field = target.slideUp("fast").closest(".form-fields-field");
            if ($field.hasClass("form-fields-field-new")) {
                $field.removeClass("form-fields-field-new").addClass("form-fields-field-new-saved");
            }
            $field.find(".form-fields-field-id").attr("value", result.userfield_id);
            $field.find(".form-fields-field-label span").html(result.userfield_label);
        }';
        $this->_ajaxOnError = 'function(xhr, result, target) {
            target.slideUp("fast");
            alert(result.message);
        }';

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->_userfield->name = $form->values['name'];
        $this->_userfield->title = $form->values['title'];
        $this->_userfield->description = $form->values['description'];
        $this->_userfield->required = $form->values['required'];
        $this->_userfield->registerable = $form->values['registerable'];
        $this->_userfield->editable = $form->values['editable'];
        $this->_userfield->visibility_default = $form->values['visibility_default'];
        $this->_userfield->visibility_control = $form->values['visibility_control'];
        $this->_userfield->settings = serialize(isset($form->values['settings']) ? $form->values['settings'] : array());

        if (!$this->_userfield->commit()) {
            $response->setError(
                $this->_('An error occurred.'),
                $this->getUrl('/user/fields')
            );
            
            return;
        }
        
        $response->setSuccess(
            $this->_('Field saved successfully.'),
            $this->getUrl('/user/fields'),
            array(
                'userfield_id' => $this->_userfield->id,
                'userfield_label' => h(mb_strimlength($this->_userfield->title, 0, 30))
            )
        );
    }

    private function _getUserField($request)
    {
        if (($userfield_id = $request->asInt('formfield_id'))
            && ($userfield = $this->getPluginModel()->Field->fetchById($userfield_id))
            && $userfield->field_id == $this->field->id
        ) {
            return $userfield;
        }
        
        $userfield = $this->getPluginModel()->create('Field');
        $userfield->field_id = $this->field->id;
        $userfield->markNew();

        return $userfield;
    }
}
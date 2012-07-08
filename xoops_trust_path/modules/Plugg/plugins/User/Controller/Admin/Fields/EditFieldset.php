<?php
class Plugg_User_Controller_Admin_Fields_EditFieldset extends Plugg_Form_Controller
{
    private $_userfield;
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $fieldset_field = $this->getPlugin('Form')->getFieldsetField();
        
        if (!$fieldset_field) return false; // this should never happen but just in case
        
        $this->_userfield = $this->_getUserField($request, $fieldset_field);

        // Define form
        $form = $this->getPluginModel()->createForm($this->_userfield);
        $form['#action'] = $this->getUrl('/user/fields/edit/fieldset');
        $form['#token'] = array('reuse' => true);

        // Generate settings form
        $current_settings = $this->_userfield->settings ? unserialize($this->_userfield->settings) : array();
        $form['settings'] = array_merge(
            $this->getPlugin('Form')->formFieldGetSettings('fieldset', $current_settings),
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
        if ($this->_userfield->id) {
            $form['name']['#collapsible'] = true;
            $form['name']['#collapsed'] = true;
            $form['userfield_id'] = array(
                '#type' => 'hidden',
                '#value' => $this->_userfield->id,
            );
            // Disable the field key field if this is the default fieldset
            if ($this->_userfield->name == 'default') {
                $form['name']['#disabled'] = true;
            }
        }
        unset($form['weight'], $form['required'], $form['editable'], $form['registerable'], $form['visibility_default'], $form['visibility_control']);
        
        // Do not require label
        $form['title']['#required'] = false;
        
        // Set options
        $this->_submitButtonLabel = $this->_('Save configuration');
        $this->_ajaxOnSuccessRedirect = false;
        $this->_ajaxCancelType = $this->_userfield->id ? 'slide' : 'none';
        $this->_ajaxOnSuccess = 'function(xhr, result, target) {
            var $fieldset = target.slideUp("fast").closest(".form-fields-fieldset");
            if ($fieldset.hasClass("form-fields-fieldset-new")) {
                $fieldset.removeClass("form-fields-fieldset-new").addClass("form-fields-fieldset-new-saved");
            }
            $fieldset.find(".form-fields-fieldset-id").attr("value", result.userfield_id);
            $fieldset.find(".form-fields-fieldset-label span").html(result.userfield_label);
        }';

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if ($this->_userfield->name != 'default') $this->_userfield->name = $form->values['name'];
        $this->_userfield->title = $form->values['title'];
        $this->_userfield->description = $form->values['description'];
        $this->_userfield->settings = serialize(isset($form->values['settings']) ? $form->values['settings'] : array());
        $this->_userfield->collapsible = $form->values['collapsible'];
        $this->_userfield->collapsed = $form->values['collapsed'];

        if (!$this->_userfield->commit()) return false;
        
        $response->setSuccess(
            $this->_('Fieldset saved successfully.'),
            $this->getUrl('/user/fields'),
            array(
                'userfield_id' => $this->_userfield->id,
                'userfield_label' => $this->_userfield->title ? h(mb_strimlength($this->_userfield->title, 0, 30)) : '&nbsp;'
            )
        );
    }

    private function _getUserField($request, $fieldsetField)
    {
        if (($userfield_id = $request->asInt('formfield_id'))
            && ($userfield = $this->getPluginModel()->Field->fetchById($userfield_id))
            && $userfield->field_id == $fieldsetField->id
        ) {
            return $userfield;
        }
        
        $userfield = $this->getPluginModel()->create('Field');
        $userfield->field_id = $fieldsetField->id;
        $userfield->markNew();
        
        return $userfield;
    }
}
<?php
class Plugg_Groups_Model_GroupForm extends Plugg_Groups_Model_Base_GroupForm
{
    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);
        
        $settings['#validate'] = array(
            array(array($this, 'validateForm'), array($entity)),
        );

        $settings['description']['#type'] = 'filters_textarea';
        $settings['description']['#default_value'] = array(
            'text' => $entity->description,
            'filter_id' => $entity->description_filter_id,
        );
        $settings['description']['#description'] = $this->_model->_('Enter a short description of the group');
        $settings['avatars'] = array(
            '#weight' => 99,
        );
        $settings['avatars']['avatar'] = array(
            '#title' => $this->_model->_('Upload avatar image'),
            '#type' => 'file',
            '#allow_only_images' => true,
            '#max_file_size' => 200, // in kilobytes
            '#thumbnail_width' => array(16, 48, 140), // generate small and medium sized thumbnails
            '#thumbnail_height' => array(16, 48, 140),
            '#upload_dir' => dirname(dirname(__FILE__))  . '/avatars',
        );
        $settings['avatars']['avatar']['#description'] = sprintf($this->_model->_('Upload an avatar image file for the group. Only files with one of the following extensions are allowed: %s'), implode(' ', array('gif', 'jpg', 'jpeg', 'png')));
        // Display current avatar image if an avatar file is already set for the group
        if ($entity->avatar) {
            $settings['avatars']['current_avatar'] = array(
                '#title' => $this->_model->_('Current avatar'),
                '#type' => 'item',
                '#markup' => $this->_model->Groups_GroupThumbnail($entity),
                '#weight' => -1,
            );
            $settings['avatars']['#title'] = $settings['avatars']['avatar']['#title'];
            unset($settings['avatars']['avatar']['#title']);
        }
        
        $settings['type']['#title'] = $this->_model->_('Group type');
        $settings['type']['#options'] = $this->_model->Groups_GroupTypes();
        if (!$entity->id) $settings['type']['#default_value'] = Plugg_Groups_Plugin::GROUP_TYPE_NONE;
        
        $settings['name']['#title'] = $this->_model->_('System name');
        $settings['name']['#collapsible'] = true;
        $settings['name']['#collapsed'] = true;
        $settings['name']['#description'] = $this->_model->_('Enter a machine readable name for this group. This value will be used in URL to access the group page. The value must be unique among all groups and only alphanumeric characters, underscores, dashes, and % signs are allowed. Leave it blank for the system to automatically generate the value.');
        $settings['name']['#regex'] = '/^[a-zA-Z0-9_\-%\+]+$/';
        
        $settings['display_name']['#weight'] = 1;
        
        unset($settings['user_id']);

        return $settings;
    }
    
    public function validateForm(Plugg_Form_Form $form, Sabai_Model_Entity $entity)
    {
        $name = $this->_model->Trim($form->values['name']);
        
        if (strlen($name) === 0) {
            $generated = true;
            $name = urlencode($form->values['display_name']);
        }
        
        if ($this->_model->Group->criteria()->name_is($name)->id_isNot($entity->id)->count()) {
            if ($generated) {
                $msg = sprintf(
                    $this->_model->_('The auto-generated system name (%s) for the group is already in use by another group. Please manually enter the system name for the group.'),
                    $name
                );
            } else {
                $msg = $this->_model->_('The system name for the group is already in use by another group.');
            }
            $form->setError($msg, 'name');
            
            return false;
        }
        
        $form->values['name'] = $name;
        
        return true;
    }
}
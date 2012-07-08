<?php
class Plugg_User_Model_RoleForm extends Plugg_User_Model_Base_RoleForm
{
    private $_permissions, $_defaultPremissions;

    public function __construct(Plugg_PluginModel $model, array $permissions, array $defaultPermissions)
    {
        parent::__construct($model);
        $this->_permissions = $permissions;
        $this->_defaultPremissions = $defaultPermissions;
    }

    public function getSettings(Sabai_Model_Entity $entity)
    {
        $settings = parent::getSettings($entity);
        
        $settings['#validate'] = array(
            array(array($this, 'validateForm'), array($entity)),
        );
        
        $settings['name']['#title'] = $this->_model->_('System name');
        $settings['name']['#collapsible'] = true;
        $settings['name']['#collapsed'] = true;
        $settings['name']['#description'] = $this->_model->_('Enter a machine readable name for this role. The value must be unique among all roles and only alphanumeric characters, underscores, dashes, and % signs are allowed. Leave it blank for the system to automatically generate the value.');
        $settings['name']['#regex'] = '/^[a-zA-Z0-9_\-%\+]+$/';
        $settings['name']['#disabled'] = $entity->system && $entity->name == 'default'; // do not allow changing the default role name
        
        $settings['display_name']['#weight'] = 1;
        
        $settings['_permissions'] = array(
            '#title' => $this->_model->_('Permissions'),
            '#tree' => true,
            '#weight' => 10,
        );
        $current_permissions = $entity->getPermissions();
        foreach (array_keys($this->_permissions) as $plugin_name) {
            $settings['_permissions'][$plugin_name] = array(
                '#type' => 'checkboxes',
                '#title' => $plugin_name,
                '#options' => $this->_permissions[$plugin_name],
                '#default_value' => !empty($current_permissions[$plugin_name]) ? $current_permissions[$plugin_name] : (!empty($this->_defaultPermissions[$plugin_name]) ? $this->_defaultPermissions[$plugin_name] : null),
            );
        }

        return $settings;
    }
    
    public function validateForm(Plugg_Form_Form $form, Sabai_Model_Entity $entity)
    {
        $name = $this->_model->Trim($form->values['name']);
        
        if (!($entity->system && $entity->name == 'default')) {
            if (strlen($name) === 0) {
                $generated = true;
                $name = urlencode($form->values['display_name']);
            }
        } else {
            $name = 'default';
        }
        
        if ($this->_model->Role->criteria()->name_is($name)->id_isNot($entity->id)->count()) {
            if ($generated) {
                $msg = sprintf(
                    $this->_model->_('The auto-generated system name (%s) for the role is already in use. Please manually enter the system name for the role.'),
                    $name
                );
            } else {
                $msg = $this->_model->_('The system name for the role is already in use.');
            }
            $form->setError($msg, 'name');
            
            return false;
        }
        
        $form->values['name'] = $name;
        
        return true;
    }
}
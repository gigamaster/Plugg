<?php
class Plugg_PluginModel extends Sabai_Model
{
    private $_application;

    public function __construct(Plugg_Plugin $plugin)
    {
        parent::__construct(
            $plugin->getDB(),
            $plugin->path . '/Model',
            'Plugg_' . $plugin->name . '_Model_'
        );
        $this->_application = $plugin->getApplication();
    }
    
    public function __call($name, $args)
    {   
        array_unshift($args, $this->_application);

        return call_user_func_array(array($this->_application->getHelper($name), 'help'), $args);
    }

    public function createForm($entity, array $params = null)
    {
        if (!$entity instanceof Sabai_Model_Entity) $entity = $this->create($entity);
        $class = $this->_modelPrefix . $entity->getName() . 'Form';
        if (!class_exists($class, false)) {
            $file = $entity->getName() . 'Form.php';
            require $this->_modelDir . '/Base/' . $file;
            require $this->_modelDir . '/' . $file;
        }

        if (!empty($params)) {
            $reflection = new ReflectionClass($class);
            array_unshift($params, $this);
            $form = $reflection->newInstanceArgs($params);
        } else {
            $form = new $class($this);
        }

        return $form->getSettings($entity);
    }
}
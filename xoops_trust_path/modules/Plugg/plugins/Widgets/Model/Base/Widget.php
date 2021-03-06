<?php
/*
This file has been generated by the Sabai scaffold script. Do not edit this file directly.
If you need to customize the class, use the following file:
plugins/Widgets/Model/Widget.php
*/
abstract class Plugg_Widgets_Model_Base_Widget extends Sabai_Model_Entity
{
    public function __construct(Sabai_Model $model)
    {
        parent::__construct('Widget', $model);
        $this->_vars = array('widget_id' => 0, 'widget_created' => 0, 'widget_updated' => 0, 'widget_name' => null, 'widget_plugin' => null, 'widget_type' => 0);
    }

    public function __clone()
    {
        $this->_vars = array_merge($this->_vars, array('widget_id' => 0, 'widget_created' => 0, 'widget_updated' => 0));
    }

    public function __toString()
    {
        return 'Widget #' . $this->_get('id', null, null);
    }

    public function addActivewidget(Plugg_Widgets_Model_Activewidget $entity)
    {
        $this->_addEntity($entity);
        return $this;
    }

    public function removeActivewidget(Plugg_Widgets_Model_Activewidget $entity)
    {
        return $this->removeActivewidgetById($entity->id);
    }

    public function removeActivewidgetById($id)
    {
        return $this->_removeEntityById('activewidget_id', 'Activewidget', $id);
    }

    public function createActivewidget()
    {
        return $this->_createEntity('Activewidget');
    }

    protected function _fetchActivewidgets($limit = 0, $offset = 0, $sort = null, $order = null)
    {
        return $this->_fetchEntities('Activewidget', $limit, $offset, $sort, $order);
    }

    protected function _fetchLastActivewidget()
    {
        if (!isset($this->_objects['LastActivewidget']) && $this->hasLastActivewidget()) {
            $this->_objects['LastActivewidget'] = $this->_fetchEntities('Activewidget', 1, 0, 'activewidget_created', 'DESC')->getFirst();
        }
        return $this->_objects['LastActivewidget'];
    }

    public function paginateActivewidgets($perpage = 10, $sort = null, $order = null)
    {
        return $this->_paginateEntities('Activewidget', $perpage, $sort, $order);
    }

    public function removeActivewidgets()
    {
        return $this->_removeEntities('Activewidget');
    }

    public function countActivewidgets()
    {
        return $this->_countEntities('Activewidget');
    }

    protected function _get($name, $sort, $order, $limit = 0, $offset = 0)
    {
        switch ($name) {
        case 'id':
            return $this->_vars['widget_id'];
        case 'created':
            return $this->_vars['widget_created'];
        case 'updated':
            return $this->_vars['widget_updated'];
        case 'name':
            return $this->_vars['widget_name'];
        case 'plugin':
            return $this->_vars['widget_plugin'];
        case 'type':
            return $this->_vars['widget_type'];
        case 'Activewidgets':
            return $this->_fetchActivewidgets($limit, $offset, $sort, $order);
        case 'LastActivewidget':
            return $this->_fetchLastActivewidget();
default:
return isset($this->_objects[$name]) ? $this->_objects[$name] : null;
        }
    }

    protected function _set($name, $value, $markDirty)
    {
        switch ($name) {
        case 'id':
            $this->_setVar('widget_id', $value, $markDirty);
            break;
        case 'name':
            $this->_setVar('widget_name', $value, $markDirty);
            break;
        case 'plugin':
            $this->_setVar('widget_plugin', $value, $markDirty);
            break;
        case 'type':
            $this->_setVar('widget_type', $value, $markDirty);
            break;
        case 'Activewidgets':
            $this->removeActivewidgets();
            foreach (array_keys($value) as $i) {
                $this->addActivewidget($value[$i]);
            }
            break;
        }
    }

    protected function _initVar($name, $value)
    {
        switch ($name) {
        default:
            $this->_vars[$name] = $value;
            break;
        }
    }
}

abstract class Plugg_Widgets_Model_Base_WidgetRepository extends Sabai_Model_EntityRepository
{
    public function __construct(Sabai_Model $model)
    {
        parent::__construct('Widget', $model);
    }

    protected function _getCollectionByRowset(Sabai_DB_Rowset $rs)
    {
        return new Plugg_Widgets_Model_Base_WidgetsByRowset($rs, $this->_model->create('Widget'), $this->_model);
    }

    public function createCollection(array $entities = array())
    {
        return new Plugg_Widgets_Model_Base_Widgets($this->_model, $entities);
    }
}

class Plugg_Widgets_Model_Base_WidgetsByRowset extends Sabai_Model_EntityCollection_Rowset
{
    public function __construct(Sabai_DB_Rowset $rs, Plugg_Widgets_Model_Widget $emptyEntity, Sabai_Model $model)
    {
        parent::__construct('Widgets', $rs, $emptyEntity, $model);
    }

    protected function _loadRow(Sabai_Model_Entity $entity, array $row)
    {
        $entity->initVars($row);
    }
}

class Plugg_Widgets_Model_Base_Widgets extends Sabai_Model_EntityCollection_Array
{
    public function __construct(Sabai_Model $model, array $entities = array())
    {
        parent::__construct($model, 'Widgets', $entities);
    }
}
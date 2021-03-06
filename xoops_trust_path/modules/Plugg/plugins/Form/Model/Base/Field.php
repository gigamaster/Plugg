<?php
/*
This file has been generated by the Sabai scaffold script. Do not edit this file directly.
If you need to customize the class, use the following file:
plugins/Form/Model/Field.php
*/
abstract class Plugg_Form_Model_Base_Field extends Sabai_Model_Entity
{
    public function __construct(Sabai_Model $model)
    {
        parent::__construct('Field', $model);
        $this->_vars = array('field_id' => 0, 'field_created' => 0, 'field_updated' => 0, 'field_type' => null, 'field_plugin' => null, 'field_system' => 0);
    }

    public function __clone()
    {
        $this->_vars = array_merge($this->_vars, array('field_id' => 0, 'field_created' => 0, 'field_updated' => 0));
    }

    public function __toString()
    {
        return 'Field #' . $this->_get('id', null, null);
    }

    public function addFormfield(Plugg_Form_Model_Formfield $entity)
    {
        $this->_addEntity($entity);
        return $this;
    }

    public function removeFormfield(Plugg_Form_Model_Formfield $entity)
    {
        return $this->removeFormfieldById($entity->id);
    }

    public function removeFormfieldById($id)
    {
        return $this->_removeEntityById('formfield_id', 'Formfield', $id);
    }

    public function createFormfield()
    {
        return $this->_createEntity('Formfield');
    }

    protected function _fetchFormfields($limit = 0, $offset = 0, $sort = null, $order = null)
    {
        return $this->_fetchEntities('Formfield', $limit, $offset, $sort, $order);
    }

    protected function _fetchLastFormfield()
    {
        if (!isset($this->_objects['LastFormfield']) && $this->hasLastFormfield()) {
            $this->_objects['LastFormfield'] = $this->_fetchEntities('Formfield', 1, 0, 'formfield_created', 'DESC')->getFirst();
        }
        return $this->_objects['LastFormfield'];
    }

    public function paginateFormfields($perpage = 10, $sort = null, $order = null)
    {
        return $this->_paginateEntities('Formfield', $perpage, $sort, $order);
    }

    public function removeFormfields()
    {
        return $this->_removeEntities('Formfield');
    }

    public function countFormfields()
    {
        return $this->_countEntities('Formfield');
    }

    protected function _get($name, $sort, $order, $limit = 0, $offset = 0)
    {
        switch ($name) {
        case 'id':
            return $this->_vars['field_id'];
        case 'created':
            return $this->_vars['field_created'];
        case 'updated':
            return $this->_vars['field_updated'];
        case 'type':
            return $this->_vars['field_type'];
        case 'plugin':
            return $this->_vars['field_plugin'];
        case 'system':
            return $this->_vars['field_system'];
        case 'Formfields':
            return $this->_fetchFormfields($limit, $offset, $sort, $order);
        case 'LastFormfield':
            return $this->_fetchLastFormfield();
default:
return isset($this->_objects[$name]) ? $this->_objects[$name] : null;
        }
    }

    protected function _set($name, $value, $markDirty)
    {
        switch ($name) {
        case 'id':
            $this->_setVar('field_id', $value, $markDirty);
            break;
        case 'type':
            $this->_setVar('field_type', $value, $markDirty);
            break;
        case 'plugin':
            $this->_setVar('field_plugin', $value, $markDirty);
            break;
        case 'system':
            $this->_setVar('field_system', $value, $markDirty);
            break;
        case 'Formfields':
            $this->removeFormfields();
            foreach (array_keys($value) as $i) {
                $this->addFormfield($value[$i]);
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

abstract class Plugg_Form_Model_Base_FieldRepository extends Sabai_Model_EntityRepository
{
    public function __construct(Sabai_Model $model)
    {
        parent::__construct('Field', $model);
    }

    protected function _getCollectionByRowset(Sabai_DB_Rowset $rs)
    {
        return new Plugg_Form_Model_Base_FieldsByRowset($rs, $this->_model->create('Field'), $this->_model);
    }

    public function createCollection(array $entities = array())
    {
        return new Plugg_Form_Model_Base_Fields($this->_model, $entities);
    }
}

class Plugg_Form_Model_Base_FieldsByRowset extends Sabai_Model_EntityCollection_Rowset
{
    public function __construct(Sabai_DB_Rowset $rs, Plugg_Form_Model_Field $emptyEntity, Sabai_Model $model)
    {
        parent::__construct('Fields', $rs, $emptyEntity, $model);
    }

    protected function _loadRow(Sabai_Model_Entity $entity, array $row)
    {
        $entity->initVars($row);
    }
}

class Plugg_Form_Model_Base_Fields extends Sabai_Model_EntityCollection_Array
{
    public function __construct(Sabai_Model $model, array $entities = array())
    {
        parent::__construct($model, 'Fields', $entities);
    }
}
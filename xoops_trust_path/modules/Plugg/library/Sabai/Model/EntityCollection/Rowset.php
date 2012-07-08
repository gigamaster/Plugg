<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Model
 * @subpackage EntityCollection
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

require_once 'Sabai/Model/EntityCollection.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Model
 * @subpackage EntityCollection
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
abstract class Sabai_Model_EntityCollection_Rowset extends Sabai_Model_EntityCollection
{
    protected $_rs;
    protected $_emptyEntity;

    public function __construct($name, Sabai_DB_Rowset $rs, Sabai_Model_Entity $emptyEntity, Sabai_Model $model)
    {
        parent::__construct($model, $name);
        $this->_rs = $rs;
        $this->_emptyEntity = $emptyEntity;
    }

    protected function _getCount()
    {
        return is_object($this->_rs) ? $this->_rs->rowCount() : 0;
    }

    public function offsetExists($index)
    {
        return is_object($this->_rs) ? $this->_rs->seek($index) : false;
    }

    public function offsetGet($index)
    {
        $entity = clone $this->_emptyEntity;
        $this->_loadRow($entity, $this->_rs->fetchAssoc());

        return $entity;
    }

    public function offsetSet($index, $value)
    {

    }

    public function offsetUnset($index)
    {

    }

    abstract protected function _loadRow(Sabai_Model_Entity $entity, array $row);
}
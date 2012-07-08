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
class Sabai_Model_EntityCollection_Array extends Sabai_Model_EntityCollection
{
    private $_entities;

    public function __construct(Sabai_Model $model, $name, array $entities = array())
    {
        parent::__construct($model, $name);
        $this->_entities = $entities;
    }

    public function offsetExists($index)
    {
        return array_key_exists($index, $this->_entities);
    }

    public function offsetGet($index)
    {
        return $this->_entities[$index];
    }

    public function offsetSet($index, $value)
    {
        $this->_entities[$index] = $value;
    }

    public function offsetUnset($index)
    {
        unset($this->_entities[$index]);
    }

    protected function _getCount()
    {
        return count($this->_entities);
    }
}
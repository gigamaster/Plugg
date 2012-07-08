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
abstract class Sabai_Model_EntityCollection_Decorator extends Sabai_Model_EntityCollection
{
    /**
     * @var Sabai_Model_EntityCollection
     */
    protected $_collection;

    /**
     * Constructor
     *
     * @param Sabai_Model_EntityCollection $collection
     * @param Sabai_Model $model
     * @return Sabai_Model_EntityCollection_Decorator
     */
    public function __construct(Sabai_Model_EntityCollection $collection)
    {
        parent::__construct($collection->getModel(), $collection->getName());
        $this->_collection = $collection;
    }

    public function offsetExists($index)
    {
        return $this->_collection->offsetExists($index);
    }

    public function offsetGet($index)
    {
        return $this->_collection->offsetGet($index);
    }

    public function offsetSet($index, $value)
    {
        $this->_collection->offsetSet($index, $value);
    }

    public function offsetUnset($index)
    {
        $this->_collection->offsetUnset($index);
    }

    protected function _getCount()
    {
        return $this->_collection->count();
    }

    public function rewind()
    {
        $this->_collection->rewind();
    }

    public function valid()
    {
        return $this->_collection->valid();
    }

    public function next()
    {
        $this->_collection->next();
    }

    public function current()
    {
        return $this->_collection->current();
    }

    public function key()
    {
        return $this->_collection->key();
    }

}
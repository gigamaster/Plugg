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
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

/**
 * Sabai_Model_Criteria_Composite
 */
require_once 'Sabai/Model/Criteria/Composite.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Model
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_Model_EntityCriteria extends Sabai_Model_Criteria_Composite
{
    private $_andOr;
    protected $_keys = array();

    /**
     * Appends a new criteria
     *
     * @param Sabai_Model_Criteria $criteria
     */
    public function add(Sabai_Model_Criteria $criteria)
    {
        switch ($this->_andOr) {
            case Sabai_Model_Criteria::CRITERIA_OR:
                $this->addOr($criteria);
                $this->_andOr = Sabai_Model_Criteria::CRITERIA_AND;
                break;
            case Sabai_Model_Criteria::CRITERIA_AND:
            default:
                $this->addAnd($criteria);
                break;
        }
        return $this;
    }

    /**
     * Adds an AND condition to the criteria
     * @return Sabai_Model_EntityCriteria
     */
    public function and_()
    {
        $this->_andOr = Sabai_Model_Criteria::CRITERIA_AND;
        return $this;
    }

    /**
     * Adds an OR condition to the criteria
     * @return Sabai_Model_EntityCriteria
     */
    public function or_()
    {
        $this->_andOr = Sabai_Model_Criteria::CRITERIA_OR;
        return $this;
    }

    /**
     * Magically adds a new criteria
     * @param string $method
     * @param array $args
     * @return Sabai_Model_EntityCriteria
     */
    public function __call($method, $args)
    {
        @list($key, $type, $key2) = explode('_', $method);
        if ($field = @$this->_keys[$key]) {
            // If second key is set, check if it has a valid field
            if (isset($key2) && ($field2 = @$this->_keys[$key2])) {
                switch ($type) {
                    case 'is':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $field2, '=', false));
                    case 'isNot':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $field2, '!=', false));
                    case 'isGreaterThan':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $field2, '>', false));
                    case 'isSmallerThan':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $field2, '<', false));
                    case 'isOrGreaterThan':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $field2, '>=', false));
                    case 'isOrSmallerThan':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $field2, '<=', false));
                }
            } else {
                switch ($type) {
                    case 'is':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $args[0]));
                    case 'isNot':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $args[0], '!='));
                    case 'isGreaterThan':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $args[0], '>'));
                    case 'isSmallerThan':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $args[0], '<'));
                    case 'isOrGreaterThan':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $args[0], '>='));
                    case 'isOrSmallerThan':
                        return $this->add(Sabai_Model_Criteria::createValue($field, $args[0], '<='));
                    case 'in':
                        return $this->add(Sabai_Model_Criteria::createIn($field, $args[0]));
                    case 'notIn':
                        return $this->add(Sabai_Model_Criteria::createNotIn($field, $args[0]));
                    case 'startsWith':
                        return $this->add(Sabai_Model_Criteria::createString($field, $args[0], '^'));
                    case 'endsWith':
                        return $this->add(Sabai_Model_Criteria::createString($field, $args[0], '$'));
                    case 'contains':
                        return $this->add(Sabai_Model_Criteria::createString($field, $args[0], $str));
                    case 'isNull':
                        return $this->add(Sabai_Model_Criteria::createIsNull($field));
                }
            }
        }

        throw new Exception(sprintf('Call to undefined method %s', $method));
    }
}
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
class Sabai_Model_Criteria
{
    const CRITERIA_AND = 'AND';
    const CRITERIA_OR = 'OR';

    /**
     * @var string
     */
    protected $_type;

    /**
     * Sets the type of criteria object
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * Accepts a gateway object as in Visitor pattern
     *
     * @param Sabai_Model_Gateway $gateway
     * @param mixed $valuePassed
     * @param bool $validateField
     */
    public function acceptGateway(Sabai_Model_Gateway $gateway, &$valuePassed, $validateField = true)
    {
        $method = 'visitCriteria' . $this->_type;
        $gateway->$method($this, $valuePassed, $validateField);
    }

    /**
     * Checks if the criteria is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return false;
    }

    public static function createComposite($elements = array(), $condition = Sabai_Model_Criteria::CRITERIA_AND)
    {
        require_once 'Sabai/Model/Criteria/Composite.php';
        return new Sabai_Model_Criteria_Composite($elements, $condition);
    }

    public static function createCompositeNot($elements = array())
    {
        require_once 'Sabai/Model/Criteria/CompositeNot.php';
        return new Sabai_Model_Criteria_CompositeNot($elements);
    }

    public static function createIn($key, $values)
    {
        require_once 'Sabai/Model/Criteria/In.php';
        return new Sabai_Model_Criteria_In($key, $values);
    }

    public static function createNotIn($key, $values)
    {
        require_once 'Sabai/Model/Criteria/NotIn.php';
        return new Sabai_Model_Criteria_NotIn($key, $values);
    }

    public static function createString($key, $string, $operator = '*')
    {
        switch($operator) {
            case '^':
                require_once 'Sabai/Model/Criteria/StartsWith.php';
                return new Sabai_Model_Criteria_StartsWith($key, $string);
            case '$':
                require_once 'Sabai/Model/Criteria/EndsWith.php';
                return new Sabai_Model_Criteria_EndsWith($key, $string);
            default:
                require_once 'Sabai/Model/Criteria/Contains.php';
                return new Sabai_Model_Criteria_Contains($key, $string);
        }
    }

    public static function createEmpty()
    {
        require_once 'Sabai/Model/Criteria/Empty.php';
        return new Sabai_Model_Criteria_Empty();
    }

    public static function createValue($key, $value, $operator = '=', $sanitize = true)
    {
        switch($operator) {
            case '<':
                require_once 'Sabai/Model/Criteria/IsSmallerThan.php';
                return new Sabai_Model_Criteria_IsSmallerThan($key, $value, $sanitize);
            case '>':
                require_once 'Sabai/Model/Criteria/IsGreaterThan.php';
                return new Sabai_Model_Criteria_IsGreaterThan($key, $value, $sanitize);
            case '<=':
                require_once 'Sabai/Model/Criteria/IsOrSmallerThan.php';
                return new Sabai_Model_Criteria_IsOrSmallerThan($key, $value, $sanitize);
            case '>=':
                require_once 'Sabai/Model/Criteria/IsOrGreaterThan.php';
                return new Sabai_Model_Criteria_IsOrGreaterThan($key, $value, $sanitize);
            case '!=':
                require_once 'Sabai/Model/Criteria/IsNot.php';
                return new Sabai_Model_Criteria_IsNot($key, $value, $sanitize);
            default:
                require_once 'Sabai/Model/Criteria/Is.php';
                return new Sabai_Model_Criteria_Is($key, $value, $sanitize);
        }
    }

    public static function createIsNull($key)
    {
        require_once 'Sabai/Model/Criteria/IsNull.php';
        return new Sabai_Model_Criteria_IsNull($key);
    }
}

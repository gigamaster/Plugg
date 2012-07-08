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
 * @subpackage Criteria
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
 * @subpackage Criteria
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
abstract class Sabai_Model_Criteria_Value extends Sabai_Model_Criteria
{
    private $_key;
    private $_value;
    private $_sanitize;

    protected function __construct($key, $value, $sanitize)
    {
        $this->_key = $key;
        $this->_value = $value;
        $this->_sanitize = $sanitize;
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function isSanitizeRequired()
    {
        return $this->_sanitize;
    }
}

<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Handle
 * @subpackage Decorator
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.7
*/

/**
 * Sabai_Handle_Decorator
 */
require_once 'Sabai/Handle/Decorator.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Handle
 * @subpackage Decorator
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.7
 */
class Sabai_Handle_Decorator_SetterInjection extends Sabai_Handle_Decorator
{
    /**
     * @var array
     */
    protected $_injections;

    /**
     * Constructor
     *
     * @param Sabai_Handle &$handle
     * @param mixed $injections array
     */
    public function __construct(Sabai_Handle $handle, array $injections)
    {
        parent::__construct($handle);
        $this->_injections = $injections;
    }

    /**
     * Gets an instance
     *
     * @return object
     */
    public function instantiate()
    {
        $ret = $this->_handle->instantiate();
        foreach (array_keys($this->_injections) as $key) {
            $func = 'set' . $key;
            $ret->$func($this->_injections[$key]);
        }
        return $ret;
    }
}
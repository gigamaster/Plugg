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
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      File available since Release 0.1.1
*/

/**
 * Sabai_Handle
 */
require_once 'Sabai/Handle.php';

/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   Sabai
 * @package    Sabai_Handle
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_Handle_Instance extends Sabai_Handle
{
    /**
     * @var object
     */
    protected $_instance;

    /**
     * Constructor
     *
     * @param object &$instance
     */
    public function Sabai_Handle_Instance($instance)
    {
        $this->_instance = $instance;
    }

    /**
     * Gets an instance
     *
     * @return object
     */
    public function instantiate()
    {
        return $this->_instance;
    }
}
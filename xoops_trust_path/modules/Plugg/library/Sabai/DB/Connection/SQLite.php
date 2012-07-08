<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_DB
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
 * @package    Sabai_DB
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_DB_Connection_SQLite extends Sabai_DB_Connection
{
    protected $_resourceMode;

    /**
     * Constructor
     *
     * @return Sabai_DB_SQLite
     */
    public function __construct(array $config)
    {
        parent::__construct('SQLite');
        $this->_resourceMode = isset($config['mode']) ? $config['mode'] : 0666;
        $this->_resourceName = $config['dbname'];
    }

    public function connect()
    {
        if (false === $link = sqlite_open($this->_resourceName, $this->_resourceMode, $error)) {
            trigger_error(sprintf('Unable to connect to database server. ERROR: %s', $error), E_USER_WARNING);
            return false;
        }
        
        $this->_resourceId = $link;    
        return true;
    }

    public function getDSN()
    {
        return sprintf('sqlite:///%s?mode=%s', rawurlencode($this->_resourceName), rawurlencode($this->_resourceMode));
    }
}
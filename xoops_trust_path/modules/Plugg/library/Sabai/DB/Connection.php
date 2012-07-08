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
abstract class Sabai_DB_Connection
{
    /**
     * @var string
     */
    protected $_scheme;
    /**
     * @var resource
     */
    protected $_resourceId;
    /**
     * @var string
     */
    protected $_resourceName;
    /**
     * @var string
     */
    protected $_clientEncoding;

    /**
     * Createa an instance of Sabai_DB_Connection
     *
     * @param string $scheme
     * @param array $params
     * @return Sabai_DB_Connection
     */
    public static function factory($scheme, array $params = array())
    {
        $scheme = str_replace('sql', 'SQL', ucfirst(strtolower($scheme)));
        $class = 'Sabai_DB_Connection_' . $scheme;
        if (!class_exists($class, false)) {
            require 'Sabai/DB/Connection/' . $scheme . '.php';
        }
        $conn = new $class($params);
        if (!$conn->connect()) {
            throw new Exception('An error occurred while connecting to the database.');
        }
        return $conn;
    }

    /**
     * Constructor
     *
     * @param string $scheme
     */
    protected function __construct($scheme)
    {
        $this->_scheme = $scheme;
    }

    /**
     * Gets the name of database scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->_scheme;
    }

    /**
     * Gets the resource handle of datasource
     *
     * @return resource
     */
    public function getResourceId()
    {
        return $this->_resourceId;
    }

    /**
     * Gets the name of datasource
     *
     * @return string
     */
    public function getResourceName()
    {
        return $this->_resourceName;
    }
    
    /**
     * Get the client connection encoding
     * 
     * @return string
     */
    public function getClientEncoding()
    {
        return $this->_clientEncoding;
    }
    
    /**
     * Magic method
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getDSN();
    }

    abstract public function connect();
    abstract public function getDSN();
}
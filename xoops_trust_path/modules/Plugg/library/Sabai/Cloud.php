<?php
/**
 * Short description for file
 *
 * Long description for file (if any)...
 *
 * LICENSE: LGPL
 *
 * @category   Sabai
 * @package    Sabai_Cloud
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
 * @package    Sabai_Cloud
 * @copyright  Copyright (c) 2006 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/lgpl-license.php GNU LGPL
 * @version    CVS: $Id:$
 * @link
 * @since      Class available since Release 0.1.1
 */
class Sabai_Cloud
{
    const SORT_NAME_ASC = 1;
    const SORT_NAME_DESC = 2;
    const SORT_COUNT_ASC = 3;
    const SORT_COUNT_DESC = 4;

    /**
     * @var array
     */
    protected $_elements = array();
    /**
     * @var array
     */
    protected $_elementLinks = array();
    /**
     * @var int
     */
    protected $_sort = self::SORT_NAME_ASC;
    /**
     * @var int
     */
    protected $_sizeMin;
    /**
     * @var int
     */
    protected $_sizeRange;
    /**
     * @var float
     */
    protected $_logMin;
    /**
     * @var float
     */
    protected $_logRange;

    /**
     * Constructor
     *
     * @param int $sizeMin
     * @param int $sizeRange
     * @return Sabai_Cloud
     */
    public function __construct($sizeMin = 10, $sizeRange = 12)
    {
        $this->_sizeMin = intval($sizeMin);
        $this->_sizeRange = intval($sizeRange);
    }

    /**
     * Sets the sort order
     *
     * @param bool $asc
     */
    public function sortByName($asc = true)
    {
        if (!$asc) {
            $this->_sort = self::SORT_NAME_DESC;
        } else {
            $this->_sort = self::SORT_NAME_ASC;
        }
    }

    /**
     * Sets the sort order
     *
     * @param bool $asc
     */
    public function sortByCount($asc = true)
    {
        if (!$asc) {
            $this->_sort = self::SORT_COUNT_DESC;
        } else {
            $this->_sort = self::SORT_COUNT_ASC;
        }
    }

    /**
     * Adds an element in the cloud
     *
     * @param string $name
     * @param string $link
     * @param int $count
     */
    public function addElement($name, $link ='', $count = 0)
    {
        $this->_elements[$name] = intval($count);
        $this->_elementLinks[$name] = $link;
    }

    /**
     * Creates the cloud
     *
     * @return array
     */
    public function build()
    {
        if (empty($this->_elements)) {
            return array();
        }
        $this->_init();
        switch($this->_sort) {
            case self::SORT_NAME_ASC:
                ksort($this->_elements, SORT_LOCALE_STRING);
                break;
            case self::SORT_NAME_DESC:
                krsort($this->_elements, SORT_LOCALE_STRING);
                break;
            case self::SORT_COUNT_ASC:
                asort($this->_elements, SORT_NUMERIC);
                break;
            case self::SORT_COUNT_DESC:
                arsort($this->_elements, SORT_NUMERIC);
                break;
            default:
                break;
        }
        $elements = array();
        foreach ($this->_elements as $name => $count) {
            $elements[] = array(
                'name' => $name,
                'count' => $count,
                'link' => $this->_elementLinks[$name],
                'size' => $this->_getSize($count)
            );
        }
        return $elements;
    }

    /**
     * Initializes cloud parameters
     */
    protected function _init()
    {
        $counts = array_values($this->_elements);
        sort($counts, SORT_NUMERIC);
        if (0 >= $count_min = array_shift($counts)) {
            $count_min = 1;
        }
        $this->_logMin = $log_max = log($count_min);
        if ($count_max = array_pop($counts)) {
            $log_max = log($count_max);
        }
        if ($this->_logMin == $log_max) {
            $this->_logRange = 1;
        } else {
            $this->_logRange = $log_max - $this->_logMin;
        }
    }

    /**
     * Calculates the size of an element by its count
     *
     * @param int $count
     * @return int
     */
    protected function _getSize($count)
    {
        $count = intval($count) <= 0 ? 1 : $count;
        $size = $this->_sizeMin + $this->_sizeRange * (log($count) - $this->_logMin) / $this->_logRange;
        return intval($size);
    }
}
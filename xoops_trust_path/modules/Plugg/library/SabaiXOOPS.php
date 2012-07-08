<?php
/**
 * Short description for class
 *
 * Long description for class (if any)...
 *
 * @category   SabaiXOOPS
 * @package    SabaiXOOPS
 * @copyright  Copyright (c) 2008 myWeb Japan (http://www.myweb.ne.jp/)
 * @author     Kazumi Ono <onokazu@gmail.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU GPL
 * @link       http://sourceforge.net/projects/sabai
 * @version    0.1.9a2
 * @since      Class available since Release 0.1.0
 */
final class SabaiXOOPS
{
    private static $_configs;

    /**
     * Gets module specific configuration object
     *
     * @static
     * @staticvar array $configs
     * @param string $moduleName
     * @param string $moduleDir
     * @param array $default
     * @return array
     */
    public static function getConfig($moduleName, $moduleDir, $default = array())
    {
        if (!isset(self::$_configs[$moduleDir])) {
            self::$_configs[$moduleDir] = array_merge(
                $default,
                self::getModuleConfig($moduleDir),
                array(
                    'localeDir' => XOOPS_TRUST_PATH . '/modules/' . $moduleName . '/locales',
                    'DB' => array(
                        'connection' => array(
                            'scheme' => XOOPS_DB_TYPE,
                            'options' => array(
                                'host' => XOOPS_DB_HOST,
                                'dbname' => XOOPS_DB_NAME,
                                'user' => XOOPS_DB_USER,
                                'pass' => XOOPS_DB_PASS,
                                'clientEncoding' => (strpos(XOOPS_VERSION, '2.0.', 1) || (defined('LEGACY_JAPANESE_ANTI_CHARSETMYSQL') && LEGACY_JAPANESE_ANTI_CHARSETMYSQL)) ? null : _CHARSET,
                            )
                        ),
                        'tablePrefix' => XOOPS_DB_PREFIX . '_' . strtolower($moduleDir) . '_' ,
                    ),
                )
            );
        }
        return self::$_configs[$moduleDir];
    }

    /**
     * Gets module specific configuration variables
     *
     * @static
     * @param string $moduleDir
     * @return array
     */
    public static function getModuleConfig($moduleDir, $configName = null)
    {
        if (self::isInModule($moduleDir) && isset($GLOBALS['xoopsModuleConfig'])) {
            $config = $GLOBALS['xoopsModuleConfig'];
        } else {
            // if not, load the module configuration variables
            if (!$module = xoops_gethandler('module')->getByDirname($moduleDir)) {
                trigger_error(sprintf('Requested module %s does not exist', $moduleDir), E_USER_ERROR);
                return;
            }
            $config = xoops_gethandler('config')->getConfigsByCat(0, $module->getVar('mid'));
        }
        
        return isset($configName) ? $config[$configName] : $config;
    }

    /**
     * Checks if the current page is within the specified module
     *
     * @param string $moduleDir
     * @return bool
     */
    public static function isInModule($moduleDir)
    {
        return isset($GLOBALS['xoopsModule']) && ($GLOBALS['xoopsModule']->getVar('dirname') == $moduleDir);
    }
}
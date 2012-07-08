<?php
require_once 'HTMLPurifier.auto.php';
require_once 'HTMLPurifier.php';

class Plugg_HTMLPurifier_ConfigFactory
{
    public static function create(array $options)
    {
        // verify cache directory
        if (empty($options['Cache.SerializerPath']) || !is_dir($options['Cache.SerializerPath'])) {
            trigger_error('Invalid HTMLPurifier cache directory.', E_USER_WARNING);
        }

        if (!is_writable($options['Cache.SerializerPath'])) {
            trigger_error(sprintf('The cache directory %s must be configured writeable by the server.', $options['Cache_SerializerPath']), E_USER_WARNING);
        }

        // remove the port part
        $host = !empty($options['URI.Host']) ? $options['URI.Host'] : $_SERVER['HTTP_HOST'];
        if ($pos = strpos($host, ':')) {
            $options['URI.Host'] = substr($host, 0, $pos);
        } else {
            $options['URI.Host'] = $host;
        }

        $options['Core.Encoding'] = SABAI_CHARSET;

        return HTMLPurifier_Config::create($options);
    }
}
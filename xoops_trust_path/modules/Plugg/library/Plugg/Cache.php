<?php
require_once 'Cache/Lite.php';

class Plugg_Cache
{
    private $_name, $_cache, $_doNotTestCacheValidity = true;

    public function __construct($cacheName, $cacheDir, $automaticSerialization, $lifetime)
    {
        $this->_name = $cacheName;
        $options = array(
            'cacheDir' => $cacheDir,
            'automaticSerialization' => $automaticSerialization,
            'lifeTime' => $lifetime,
        );
        $this->_cache = new Cache_Lite($options);
        if (!empty($lifetime)) $this->_doNotTestCacheValidity = false;
    }

    public function get($id)
    {
        return $this->_cache->get($id, $this->_name, $this->_doNotTestCacheValidity);
    }

    public function save($data, $id)
    {
        return $this->_cache->save($data, $id, $this->_name);
    }

    public function remove($id)
    {
        return $this->_cache->remove($id, $this->_name);
    }

    public function clean()
    {
        return $this->_cache->clean($this->_name);
    }
}
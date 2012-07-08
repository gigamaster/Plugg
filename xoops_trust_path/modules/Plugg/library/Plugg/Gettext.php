<?php
class Plugg_Gettext extends Sabai_Gettext
{
    private $_localeDir, $_cache;

    public function __construct($plugg)
    {
        $this->_localeDir = Plugg::$localeDir;
        $this->_cache = $plugg->getLocator()->getService('Cache');
        $this->textdomain($plugg->getId());
    }

    public function loadMessages($pluginName, $moFile, $useCache = true)
    {
        if (!$useCache) {
            return $this->bindtextdomain($pluginName, $this->_localeDir, $moFile);
        }

        // Try fetching cached messages
        $cache_id = $this->_getCacheId($pluginName);
        if (!$cached = $this->_cache->get($cache_id)) {
            // No cache, so load messages from gettext file and then cache messages and plural func expression
            if ($this->bindtextdomain($pluginName, $this->_localeDir, $moFile)) {
                return $this->_cache->save(serialize(array($this->_messages[$pluginName], $this->_pluralFuncExpressions[$pluginName])), $cache_id);
            }

            return false;
        }

        // Load messages from cache
        list($messages, $plural_func_expr) = unserialize($cached);
        $this->_messages[$pluginName] = $messages;
        $this->_pluralFuncs[$pluginName] = create_function('$n', $plural_func_expr);
        $this->_pluralFuncExpressions[$pluginName] = $plural_func_expr;

        return true;
    }

    public function updateCachedMessages($messages, $pluginName = null)
    {
        if (!isset($pluginName)) $pluginName = $this->_defaultDomain;

        $cache_id = $this->_getCacheId($pluginName);
        if (!$cached = $this->_cache->get($cache_id)) {
            // Not yet cached
            return false;
        }

        list(, $plural_func_expr) = unserialize($cached);
        return $this->_cache->save(serialize(array($messages, $plural_func_expr)), $cache_id);
    }

    public function clearCachedMessages($pluginName = null)
    {
        if (!isset($pluginName)) $pluginName = $this->_defaultDomain;

        $cache_id = $this->_getCacheId($pluginName);
        if (!$cached = $this->_cache->get($cache_id)) {
            // Not cached
            return true;
        }

        return $this->_cache->remove($cache_id);
    }

    private function _getCacheId($pluginName)
    {
        return SABAI_LANG . SABAI_CHARSET . $pluginName;
    }
}
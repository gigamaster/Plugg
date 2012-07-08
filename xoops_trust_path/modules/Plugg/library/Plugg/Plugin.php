<?php
abstract class Plugg_Plugin extends SabaiPlugin_Plugin
{
    protected $_application, $_hasSchema, $_hasModel, $_hasMainCSS, $_hasAdminCSS, $_hasLocale, $_nicename, $_config;

    public function __construct($name, $path, $version, $extra, Plugg $application)
    {
        parent::__construct($name, $path, $version);
        $this->_nicename = @$extra['nicename'];
        $this->_hasSchema = $extra['hasSchema'];
        $this->_hasModel = $extra['hasModel'];
        $this->_hasMainCSS = $extra['hasMainCSS'];
        $this->_hasAdminCSS = $extra['hasAdminCSS'];
        $this->_hasLocale = $extra['hasLocale'];
        if (isset($extra['params'])) {
            $this->_config = $extra['params']; 
        }
        $this->_application = $application;
        $this->_application->getGettext()->loadMessages($this->_name, $this->_name . '.mo');
    }

    public function getApplication()
    {
        return $this->_application;
    }

    final public function getConfig($name = null)
    {
        $args = isset($name) && is_array($name) ? $name : func_get_args();
        $config = $this->_config;
        foreach ($args as $arg) {
            $config = isset($config[$arg]) ? $config[$arg] : null;
        }

        return $config;
    }
    
    public function getDefaultConfig()
    {
        // Override this to provide default configuration parameters
        return array();
    }

    final public function hasLocale()
    {
        return $this->_hasLocale;
    }

    final public function _($msgId)
    {
        if ($this->_application->getGettext()->dhastext($this->_name, $msgId)) {
            return $this->_application->getGettext()->dgettext($this->_name, $msgId);
        }

        // If message cannt be found in the domain, find it from the global domain
        return $this->_application->getGettext()->gettext($msgId);
    }

    final public function _e($msgId)
    {
        if ($this->_application->getGettext()->dhastext($this->_name, $msgId)) {
            echo $this->_application->getGettext()->dgettext($this->_name, $msgId);
        } else {
            // If message cannt be found in the domain, find it from the global domain
            echo $this->_application->getGettext()->gettext($msgId);
        }
    }

    final public function _n($msgId, $msgId2, $num)
    {
        if ($this->_application->getGettext()->dhastext($this->_name, $msgId)) {
            return $this->_application->getGettext()->dngettext($this->_name, $msgId, $msgId2, $num);
        }

        // If message cannt be found in the domain, find it from the global domain
        return $this->_application->getGettext()->ngettext($msgId, $msgId2, $num);
    }
    
    final public function setSessionVar($name, $value)
    {
        $_SESSION[$this->_application->getId()][$this->_name][$name] = $value;
    }

    final public function getSessionVar($name)
    {
        return @$_SESSION[$this->_application->getId()][$this->_name][$name];
    }

    final public function hasSessionVar($name)
    {   
        $app_id = $this->_application->getId();
        return isset($_SESSION[$app_id][$this->_name])
            && is_array($_SESSION[$app_id][$this->_name])
            && array_key_exists($name, $_SESSION[$app_id][$this->_name]);
    }

    final public function unsetSessionVar($name)
    {
        unset($_SESSION[$this->_application->getId()][$this->_name][$name]);
    }

    final public function clearSession()
    {
        $_SESSION[$this->_application->getId()][$this->_name] = array();
    }

    private function _getSchemaList($schemaDir)
    {

        if (!$dh = opendir($schemaDir)) {
            return false;
        }

        $old = $new = array();
        while ($file = readdir($dh)) {
            if (preg_match('/^\d+(?:\.\d+)*(?:[a-zA-Z]+\d*)?\.xml$/', $file)) {
                $file_version = basename($file, '.xml');
                $inserted = false;
                if (version_compare($file_version, $this->_version, '<=')) {
                    if (!empty($old)) {
                        $old2 = array();
                        foreach ($old as $old_version => $old_file) {
                            if (!$inserted && version_compare($file_version, $old_version, '<')) {
                                $old2[$file_version] = $schemaDir . $file;
                                $inserted = true;
                            }
                            $old2[$old_version] = $old_file;
                        }
                        if (!$inserted) {
                            $old2[$file_version] = $schemaDir . $file;
                        }
                        $old = $old2;
                    } else {
                        $old = array($file_version => $schemaDir . $file);
                    }
                } else {
                    if (!empty($new)) {
                        $new2 = array();
                        $found = false;
                        foreach ($new as $new_version => $new_file) {
                            if (!$found && version_compare($file_version, $new_version, '<')) {
                                $new2[$file_version] = $schemaDir . $file;
                                $found = true;
                            } else {
                                $new2[$new_version] = $new_file;
                            }
                        }
                        if (!$found) {
                            $new2[$file_version] = $schemaDir . $file;
                        }
                        $new = $new2;
                    } else {
                        $new = array($file_version => $schemaDir . $file);
                    }
                }
            }
        }

        return array($old, $new);
    }

    final public function install(&$message)
    {
        if ($this->_hasSchema) {
            // create database tables
            $schema = $this->_getDBSchema();
            if (!$schema->create($this->_path . '/schema/latest.xml')) {
                $message = sprintf('Failed creating database tables using schema. Error: %s', implode(', ', $schema->getErrors()));
                return false;
            }
            $message = 'Database tables created.';
        }
        // Load message catalogue
        $this->_application->getGettext()->loadMessages($this->_name, $this->_name . '.mo');
        
        return true;
    }

    final public function uninstall(&$message)
    {
        if ($this->_hasSchema) {
            $schema_dir = $this->_path . '/schema/';
            if (false === $schema_list = $this->_getSchemaList($schema_dir)) {
                $message = 'Failed opening schema directory.';
                return false;
            }

            list($schema_old, $schema_new) = $schema_list;
            if (!empty($schema_old)) {
                // get the last schema file
                $previous_schema = array_pop($schema_old);
            } else {
                $previous_schema = $schema_dir . 'latest.xml';
            }
            $schema = $this->_getDBSchema();
            if (!$schema->drop($previous_schema)) {
                $message = sprintf(
                    'Failed deleting database tables using schema %s. Error: %s',
                    str_replace($schema_dir, '', $previous_schema),
                    implode(', ', $schema->getErrors())
                );
                return false;
            }
            $message = 'Database tables deleted.';
        }
        if (!$this->_getCacheObject()->clean()) {
            $message .= 'Failed removing cache files.';
        } else {
            $message .= 'Removed cache files.';
        }
        // Clear message catalogue
        $this->_application->getGettext()->clearCachedMessages($this->_name);
        // Clear cache
        $this->removeCache();
        
        return true;
    }

    final public function upgrade($previousVersion, &$message)
    {
        if ($this->_hasSchema) {
            $schema_dir = $this->_path . '/schema/';
            if (false === $schema_list = $this->_getSchemaList($schema_dir)) {
                $message = 'Failed opening schema directory.';
                return false;
            }

            list($schema_old, $schema_new) = $schema_list;

            if (!empty($schema_new)) {
                $schema = $this->_getDBSchema();
                $messages = array();
                if (!empty($schema_old)) {
                    // get the last schema file
                    $previous_schema = array_pop($schema_old);
                } else {
                    // No old schema, so get one from the new schema list
                    $previous_schema = array_shift($schema_new);
                    if (!$schema->create($previous_schema)) {
                        $message = sprintf('Failed creating database tables using schema. Error: %s', implode(', ', $schema->getErrors()));
                        return false;
                    }
                    $messages[] = sprintf('Created database using schema %s.', str_replace($schema_dir, '', $previous_schema));
                }
                // update schema incrementally

                foreach ($schema_new as $new_schema) {
                    if (!$result = $schema->update($new_schema, $previous_schema)) {
                        $message = sprintf(
                            'Failed updating database schema from %s to %s. Error: %s',
                            str_replace($schema_dir, '', $previous_schema),
                            str_replace($schema_dir, '', $new_schema),
                            implode(', ', $schema->getErrors())
                        );
                        return false;
                    }
                    $messages[] = sprintf(
                        'Updated database schema from %s to %s.',
                        str_replace($schema_dir, '', $previous_schema),
                        str_replace($schema_dir, '', $new_schema)
                    );
                    $previous_schema = $new_schema;
                }
                $message = implode('<br />', $messages);
            }
        }
        // Load message catalogue
        $this->_application->getGettext()->loadMessages($this->_name, $this->_name . '.mo');
        
        return isset($new_schema) ? basename($new_schema, '.xml') : $previousVersion;
    }

    final public function getDB()
    {
        return $this->_application->getLocator()->getService('DB', $this->_name, array(
            'tablePrefix' => $this->_application->getLocator()->getDefaultParam('DB', 'tablePrefix') . strtolower($this->_name) . '_'
        ));
    }

    protected function _getDBSchema()
    {
        return Sabai_DB_Schema::factory($this->getDB());
    }

    final public function getModel()
    {
        if (!$this->_hasModel) throw new Plugg_Exception(sprintf('No model available for plugin %s', $this->_name));

        return $this->_application->getLocator()->getService('PluginModel', $this->_name, array('plugin' => $this));
    }

    /**
     * @param int $lifetime cache lifetime in seconds, Null for no expiration
     */
    final protected function _getCacheObject($lifetime = null)
    {
        return $this->_application->getLocator()->getService('Cache', $this->_name, array(
            'cacheName' => $this->_name,
            'lifeTime' => $lifetime,
        ));
    }

    final public function getCache($id)
    {
        return $this->_getCacheObject()->get($id);
    }

    final public function saveCache($data, $id, $lifetime = null)
    {
        return $this->_getCacheObject($lifetime)->save($data, $id);
    }

    final public function removeCache($id = null)
    {
        return isset($id) ? $this->_getCacheObject()->remove($id) : $this->_getCacheObject()->clean();
    }

    final public function loadConfig()
    {
        if ($plugin = $this->_application->getPlugin('System')->getModel()->Plugin->fetchByName($this->_name)) {
             return false;
        }

        $this->_config = $plugin->getParams();

        return true;
    }

    final public function saveConfig(array $params, array $paramsNoCache = array(), $merge = true)
    {
        if (!$plugin = $this->_application->getPlugin('System')->getModel()->Plugin->fetchByName($this->_name)) {
            return false;
        }

        $params_original = $plugin->getParams();
        $plugin->setParams($params, $paramsNoCache, $merge);

        if (!$plugin->commit()) return false;

        // Reload plugins
        $this->_application->getPluginManager()->reloadPlugins();

        // Load messages
        $this->_application->getGettext()->loadMessages($this->_name, $this->_name . '.mo');

        // Dispatch plugin configured events
        $this->_application->DispatchEvent('SystemAdminPluginConfigured',array($plugin, $params_original));
        $this->_application->DispatchEvent($this->_name . 'PluginConfigured', array($plugin, $params_original));

        return true;
    }

    final public function getRequestedEntity(Sabai_Request $request, $entityName, $entityIdVar = null, $noCache = false)
    {
        $entity_name_lc = strtolower($entityName);
        $entity_idvar = !isset($entityIdVar) ? $entity_name_lc . '_id' : $entityIdVar;
        if (0 < $entity_id = $request->asInt($entity_idvar)) {
            return $this->_getEntity($request, $entityName, $entity_id, $noCache);
        }

        return false;
    }

    final public function getEntity(Sabai_Request $request, $entityName, $entityId, $noCache = false)
    {
        return $this->_getEntity($request, $entityName, $entityId, $noCache = false);
    }

    private function _getEntity(Sabai_Request $request, $entityName, $entityId, $noCache = false)
    {
        $repository = $this->getModel()->$entityName;
        if (false !== $entity = $repository->fetchById($entityId, $noCache)) {
            $repository->cacheEntity($entity);
        }

        return $entity;
    }

    final public function getUrl($path = '', array $params = array(), $fragment = '', $separator = '&amp;')
    {
        $path = '/' . strtolower($this->_name) . '/' . $path;

        return $this->_application->getUrl($path, $params, $fragment, $separator);
    }
}
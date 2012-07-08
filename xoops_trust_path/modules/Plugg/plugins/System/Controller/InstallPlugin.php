<?php
class Plugg_System_Controller_InstallPlugin extends Plugg_Form_Controller
{
    protected $_interface, $_plugins, $_installedPlugins;
    
    protected function __construct($interface = null)
    {
        $this->_interface = $interface;
    }
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $refresh_cache = $request->asBool('refresh');
        if (!isset($this->_interface)) {
            $this->_plugins = $this->getPluginManager()->getLocalPlugins($refresh_cache);
        } else {
            $this->_plugins = $this->getPluginManager()->getLocalPluginsByInterface($this->_interface, true, $refresh_cache);
        }
        $this->_installedPlugins = $this->getPluginManager()->getInstalledPlugins(true);
        $form = array('#enable_storage' => true);
        
        if (!empty($formStorage['uninstall_plugins'])) {
            $form['#header'][] = sprintf('<div class="plugg-warning">%s</div>', $this->_('Are you sure you want to uninstall these plugins?'));
            $form['plugins'] = array(
                '#type' => 'item',
                '#title' => $this->_('Plugins to uninstall:'),
                '#markup' => implode(', ', $formStorage['uninstall_plugins'])
            );
            $this->_submitButtonLabel = $this->_('Yes, uninstall');
            $this->_cancelUrl = $this->getUrl($this->getRequestedRoute());
            
            return $form;
        }
        
        $this->_submitButtonLabel = $this->_('Install');
        $this->_cancelUrl = null;
        $form['plugins'] = array(
            '#type' => 'tableselect',
            '#header' => array('plugin' => $this->_('Plugin'), 'links' => ''),
            '#multiple' => true,
            '#options' => array(),
        );

        foreach ($this->_plugins as $plugin_name => $plugin_data) {
            $plugin_is_upgradable = false;
            if (!isset($this->_installedPlugins[$plugin_name])) {
                $form['plugins']['#attributes'][$plugin_name]['@row']['class'] = 'shadow';
                $version = $plugin_data['version'];
            } else {
                $version = $this->_installedPlugins[$plugin_name]['version'];
                // Highlight row if the plugin is upgradable
                if (version_compare($version, $plugin_data['version'], '<')) {
                    $is_upgradable = $plugin_is_upgradable = true;
                }
            }
            $links = array(
                $this->LinkToRemote($this->_('Details'), 'plugg-content', $this->getUrl('/system/plugins/plugin/' . $plugin_name))
            );
            if (!empty($plugin_data['author'])) {
                if (!empty($plugin_data['author_url'])) {
                    $author = sprintf('by <a href="%s">%s</a>', h($plugin_data['author_url']), h($plugin_data['author']));
                } else {
                    $author = 'by ' . h($plugin_data['author']);
                }
            } else {
                $author = '';
            }

            $form['plugins']['#options'][$plugin_name] = array(
                'plugin' => sprintf(
                    '<strong>%s v%s</strong> %s<p>%s%s</p>%s',
                    h($plugin_name),
                    h($version),
                    $author,
                    h(mb_strimlength($plugin_data['description'], 0, 200)),
                    empty($plugin_data['required_plugins']) ? '' : '<br /><small>' . sprintf($this->_('requires %s'), h(implode(', ', $plugin_data['required_plugins']))) . '</small>',
                    $plugin_is_upgradable ? '<div class="plugg-warning">' . sprintf($this->_('There is a new version (%s) available for this plugin.'), $plugin_data['version']) . '</div>' : ''
                ),
                'links' => implode(PHP_EOL, $links),
            );
        }

        if (!empty($is_upgradable)) {
            $form[$this->_submitButtonName]['upgrade'] = array(
                '#type' => 'submit',
                '#value' => $this->_('Upgrade'),
                '#submit' => array(array($this, 'upgradePlugins')),
                '#weight' => -2,
            );
        }
            
        $form[$this->_submitButtonName]['uninstall'] = array(
            '#weight' => -1,
            '#type' => 'submit',
            '#value' => $this->_('Uninstall'),
            '#validate' => array(array(array($this, 'validateUninstall'), array($request))),
            '#submit' => array(array(array($this, 'uninstallPlugins'), array($request))),
        );

        return $form;
    }
    
    public function validateForm(Plugg_Form_Form $form, Sabai_Request $request)
    {
        if (isset($form->storage['uninstall_plugins'])) {
            return $this->_validateUninstall($form, $request);
        }
        
        return $this->_validateInstall($form, $request);
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (isset($form->storage['uninstall_plugins'])) {
            return $this->_uninstall($form, $request, $response);
        }
        
        return $this->_install($form, $request, $response);
    }
    
    private function _validateInstall(Plugg_Form_Form $form, Sabai_Request $request)
    {
        $form->values['plugins'] = array_diff($form->values['plugins'], array_keys($this->_installedPlugins));
        
        if (empty($form->values['plugins'])) return true;
        
        foreach ($form->values['plugins'] as $plugin) {
            $error = '';
            if (false === $this->getPlugin('System')->isPluginInstallable($plugin, $error)) {
                $form->setError(sprintf($this->_('An error occurred while installing the %s plugin. Error: %s'), $plugin, $error), 'plugins');
                
                return false;
            }
        }
        
        return true;
    }
    
    private function _install(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['plugins'])) return true;
        
        $plugins_installed = array();
        foreach ($form->values['plugins'] as $plugin) {
            if (!isset($this->_plugins[$plugin])) {
                $failed_plugin = $plugin;
                $failed_plugin_error = $this->_('Invalid plugin.');
                break;
            }
            
            $result = $this->getPlugin('System')->installPlugin($plugin, $this->_plugins[$plugin]);
            if (!is_object($result)) {
                $failed_plugin = $plugin;
                $failed_plugin_error = $result;
                break;
            }
            $plugins_installed[$plugin] = $result;
            
            // chmod directories or files if configured and display warning messages if chmod fails
            if (!empty($this->_plugins[$plugin]['chmod'])) {
                foreach ((array)$this->_plugins[$plugin]['chmod'] as $file) {
                    if (!file_exists($file) || !@chmod($file, is_dir($file) ? 0777 : 0666)) {
                        $failed_chmod[] = $file;
                    }
                }
            }
        }
        
        if (isset($failed_plugin)) {
            $form->setError(sprintf($this->_('An error occurred while installing the %s plugin. Error: %s', $failed_plugin, $failed_plugin_error)), 'plugins');
            foreach (array_keys($plugins_installed) as $plugin_installed) {
                $result = $this->getPlugin('System')->uninstallPlugin($plugin_installed);
                if (!is_object($result)) {
                    $form->setError(sprintf(
                        $this->_('Failed uninstalling the following plugin: %s. You must manually uninstalled the plugin. Error: %s'),
                        $plugin_installed,
                        $result
                    ));
                }
            }
            
            return false;
        }
        
        if (!empty($failed_chmod)) {
            foreach ($failed_chmod as $file) {
                $response->addMessage(
                    sprintf($this->_('The permission of %s must be changed so that it is writeable by the web server.'), $file),
                    Sabai_Application_Response::MESSAGE_WARNING
                );
            }
        }
        
        foreach ($plugins_installed as $plugin_installed => $plugin_entity) {
            $this->DispatchEvent('SystemAdminPluginInstalled', array($plugin_entity));
            $this->DispatchEvent($plugin_installed . 'Installed', array($plugin_entity));
        }
        
        // Reload plugins data
        $this->getPluginManager()->reloadPlugins();
        
        return true;
    }
    
    public function validateUninstall(Plugg_Form_Form $form, Sabai_Request $request)
    {
        $form->values['plugins'] = array_intersect($form->values['plugins'], array_keys($this->_installedPlugins));
        
        return $this->_doValidateUninstall($form);
    }
    
    private function _validateUninstall(Plugg_Form_Form $form, Sabai_Request $request)
    {
        $form->values['plugins'] = array_intersect($form->storage['uninstall_plugins'], array_keys($this->_installedPlugins));
        
        return $this->_doValidateUninstall($form);
    }
    
    private function _doValidateUninstall(Plugg_Form_Form $form)
    {
        if (empty($form->values['plugins'])) return true;
        
        foreach ($form->values['plugins'] as $plugin) {
            $error = '';
            if (false === $this->getPlugin('System')->isPluginUninstallable($plugin, $error)) {
                $form->setError(sprintf($this->_('An error occurred while trying to uninstall the %s plugin. Error: %s'), $plugin, $error), 'plugins');
                
                return false;
            }
        }
        
        return true;
    }
    
    private function _uninstall(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['plugins'])) return true;

        $plugins_uninstalled = array();
        foreach ($form->values['plugins'] as $plugin) {
            if (!isset($this->_plugins[$plugin])) {
                $failed_plugin = $plugin;
                $failed_plugin_error = $this->_('Invalid plugin.');
                break;
            }
            $result = $this->getPlugin('System')->uninstallPlugin($plugin);
            if (!is_object($result)) {
                $failed_plugin = $plugin;
                $failed_plugin_error = $result;
                break;
            }
            
            $plugins_uninstalled[$plugin] = $result;
        }
        
        foreach ($plugins_uninstalled as $plugin_uninstalled => $plugin_entity) {
            $this->DispatchEvent('SystemAdminPluginUninstalled', array($plugin_entity));
            $this->DispatchEvent($plugin_uninstalled . 'Uninstalled', array($plugin_entity));
        }

        // Reload plugins data
        $this->getPluginManager()->reloadPlugins();
        
        if (isset($failed_plugin)) {
            $form->setError(
                sprintf($this->_('An error occurred while uninstalling the %s plugin. Error: %s'), $failed_plugin, $failed_plugin_error),
                'plugins'
            );
            
            return false;
        }

        return true;
    }
    
    public function uninstallPlugins(Plugg_Form_Form $form, Sabai_Request $request)
    {
        $form->storage['uninstall_plugins'] = $form->values['plugins'];
        $form->rebuild = true;
        
        return false;
    }
    
    public function upgradePlugins(Plugg_Form_Form $form)
    {
        if (empty($form->values['plugins'])) return true;
        
        $plugins_upgraded = array();
        foreach ($form->values['plugins'] as $plugin) {
            if (!isset($this->_plugins[$plugin])) {
                $failed_plugin = $plugin;
                $failed_plugin_error = $this->_('Invalid plugin.');
                break;
            }
            $result = $this->getPlugin('System')->upgradePlugin($plugin, $this->_plugins[$plugin]);
            if (!is_object($result)) {
                $failed_plugin = $plugin;
                $failed_plugin_error = $result;
                break;
            }
            
            $plugins_upgraded[$plugin] = $result;
        }
        
        foreach ($plugins_upgraded as $plugin_upgraded => $plugin_entity) {
            $this->DispatchEvent('SystemAdminPluginUpgraded', array($plugin_entity));
            $this->DispatchEvent($plugin_upgraded . 'Upgraded', array($plugin_entity));
        }

        // Reload plugins data
        $this->getPluginManager()->reloadPlugins();
        
        if (isset($failed_plugin)) {
            $form->setError(
                sprintf($this->_('An error occurred while upgrading the %s plugin. Error: %s'), $failed_plugin, $failed_plugin_error),
                'plugins'
            );
            
            return false;
        }

        return true;
    }
}
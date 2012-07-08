<?php
class Plugg_Search_Controller_Admin_System_Settings_Engine_Configure extends Plugg_Form_Controller
{   
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            '#id' => 'search_admin_settings_engine_' . strtolower($this->engine->plugin),
            $this->engine->plugin => array_merge(
                $this->getPlugin($this->engine->plugin)->searchEngineGetSettings(),
                array(
                    '#type' => 'fieldset',
                    '#tree' => true,
                    '#tree_allow_override' => false,
                )
            ),
            'searchables' => array(
                '#type' => 'tableselect',
                '#title' => $this->_('Searchable content'),
                '#header' => array(
                    'name' => $this->_('Name'),
                    'plugin' => $this->_('Plugin'),
                    'entries' => $this->_('Entries'),
                    'links' => '',
                ),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
                '#default_value' => $this->engine->Searchables->getAllIds(),
                '#empty' => $this->_('You do not have any plugins that implement the Plugg search interface installed.')
            )
        );
        
        // Add rows (options)
        $searchables = $this->getPluginModel()->Searchable->fetch(0, 0, array('plugin', 'name'), array('ASC', 'ASC'));
        foreach ($searchables as $searchable) {
            if (!$searchable_plugin = $this->getPlugin($searchable->plugin)) continue;
            $links = array(
                $this->LinkToRemote(
                    $this->_('Import'),
                    'plugg-content',
                    $this->getUrl('/system/settings/search/' . $this->engine->id . '/' . $searchable->id)
                ),
            );
            $form['searchables']['#options'][$searchable->id] = array(
                'name' => h(sprintf(
                    $searchable_plugin->searchGetNicename($searchable->name),
                    $searchable_plugin->nicename
                )),
                'plugin' => $searchable->plugin,
                'members' => '0/0',
                'links' => implode(PHP_EOL, $links),
            );
        }
        
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values[$this->engine->plugin])) {
            if (!$this->getPlugin($this->engine->plugin)->saveConfig($form->values[$this->engine->plugin])) {
                return false;
            }
        }
        
        // Save searchable content configuration for the search engine plugin
        $this->engine->unlinkSearchables(); // reset
        if (!empty($form->values['searchables'])) {
            foreach ($form->values['searchables'] as $searchable_id) {
                $this->engine->linkSearchableById($searchable_id);
            }
        }
        if (!$this->engine->getModel()->commit()) {
            $form->setError($this->_('An error occurred while saving data.'), 'searchables');
                
            return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}
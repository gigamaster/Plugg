<?php
class Plugg_Search_Controller_Admin_System_Settings extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $engine_options = array();
        foreach ($this->getPluginModel()->Engine->fetch() as $engine) {
            if (!$engine_plugin = $this->getPlugin($engine->plugin)) continue;
            $engine_options[$engine->plugin] = array(
                'title' => h($engine_plugin->nicename),
                'summary' => '',
                'links' => $engine_plugin->searchEngineGetSettings() ? $this->LinkTo($this->_('Configure'), $this->getUrl('/system/settings/search/' . $engine->id)) : '',
            );
        }

        $form = array(
            'Search' => array(
                '#tree' => true,
                'searchEnginePlugin' => array(
                    '#title' => $this->_('Search engine'),
                    '#description' => $this->_('Select the search engine plugin to use for searching content.'),
                    '#type' => 'tableselect',
                    '#options' => $engine_options,
                    '#header' => array('title' => $this->_('Title'), 'summary' => $this->_('Summary'), 'links' => ''),
                    '#required' => true,
                    '#default_value' => $this->getPlugin()->getConfig('searchEnginePlugin'),
                ),
                'keywordMinLength' => array(
                    '#title' => $this->_('Minimum length of search keyword'),
                    '#description' => $this->_('Select the minimum text length of each search keyword the user must enter to perform search.'),
                    '#default_value' => $this->getPlugin()->getConfig('keywordMinLength'),
                    '#type' => 'radios',
                    '#options' => array(1 => 1, 2 => 2, 3 => 3, 5 => 5, 10 => 10, 20 => 20),
                    '#delimiter' => '&nbsp;',
                    '#numeric' => true,
                ),
                'numResultsPage' => array(
                    '#title' => $this->_('Number of search results to display'),
                    '#description' => $this->_('Select the default number of search results to display per page.'),
                    '#default_value' => $this->getPlugin()->getConfig('numResultsPage'),
                    '#type' => 'radios',
                    '#options' => array(1 => 1, 5 => 5, 10 => 10, 20 => 20, 30 => 30, 50 => 50),
                    '#delimiter' => '&nbsp;',
                    '#numeric' => true,
                ),
            ),
        );
        
        $this->_cancelUrl = null;
        $this->_submitButtonLabel = $this->_('Save configuration');

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (!empty($form->values['Search'])) {
            if (!$this->getPlugin()->saveConfig($form->values['Search'])) return false;
        }

        $response->setSuccess(
            $this->_('The configuration options have been saved.'),
            $request->getUrl()
        );

        return true; // submit success
    }
}
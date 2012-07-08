<?php
class Plugg_Search_Controller_Main_Index extends Plugg_Form_Controller
{
    private $_searchables, $_keywords, $_keywordsFailed, $_results;
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {   
        $this->_searchables = $this->getPlugin()->getActiveSearchables();
        $this->_cancelUrl = null;
        $this->_submitButtonLabel = $this->_('Search');
        
        $engine = $this->getPlugin()->getEnginePlugin();
        $engine_features = $engine->searchEngineGetFeatures();
        
        $form = array(
            '#method' => 'get',
            '#action' => $this->getUrl('/search', array(), 'plugg-search-results'),
            '#token' => false,
            'keyword' => array(
                '#type' => 'textfield',
                '#title' => $this->_('Find contents that have:'),
                '#size' => 60,
                '#required' => true,
            ),
            'keyword_not' => array(
                '#type' => $engine_features & Plugg_Search_Plugin::ENGINE_FEATURE_BOOLEAN_NOT ? 'textfield' : 'hidden',
                '#title' => $this->_('But do not show contents that have any of these unwanted words:'),
                '#size' => 60,
            ),
        );
        
        // Fetch keyword type options that the engine plugin supports
        $keywords_type_options = array();
        if ($engine_features & Plugg_Search_Plugin::ENGINE_FEATURE_BOOLEAN_AND) {
            $keywords_type_options[Plugg_Search_Plugin::KEYWORDS_AND] = $this->_('all of the above words');
        }
        if ($engine_features & Plugg_Search_Plugin::ENGINE_FEATURE_BOOLEAN_OR) {
            $keywords_type_options[Plugg_Search_Plugin::KEYWORDS_OR] = $this->_('one of the above words');
        }
        
        if (!empty($keywords_type_options)) {
            $form['keyword_type'] = array(
                '#type' => 'radios',
                '#options' => $keywords_type_options,
                '#default_value' => array_shift(array_keys($keywords_type_options)),
                '#delimiter' => '&nbsp;',
            );
        }

        if ($engine_features & Plugg_Search_Plugin::ENGINE_FEATURE_FIND_BY_SEARCHABLES) {
            // Searchable content selection
            $searchable_options = array();
            foreach (array_keys($this->_searchables) as $searchable_id) {
                $searchable = $this->_searchables[$searchable_id];
                $searchable_options[$searchable_id] = $searchable['title'];
            }
            if (!empty($searchable_options)) {
                $form['s_'] = array(
                    '#type' => 'checkboxes',
                    '#title' => $this->_('Only of the type(s):'),
                    '#options' => $searchable_options,
                    '#default_value' => array_keys($searchable_options),
                    '#delimiter' => '&nbsp;',
                );
            }
        } elseif (($engine_features & Plugg_Search_Plugin::ENGINE_FEATURE_FIND_BY_PLUGINS)
            && ($searchable_plugins = $this->getActiveSearchablePlugins($private))
        ) {
            // Searchable content selection
            $searchable_options = array();
            foreach (array_keys($searchable_plugins) as $plugin_name) {
                $searchable_options[$plugin_name] = $searchable_plugins[$plugin_name];
            }
            if (!empty($searchable_options)) {
                $form['_p'] = array(
                    '#type' => 'checkboxes',
                    '#title' => $this->_('Only of the type(s):'),
                    '#options' => $searchable_options,
                    '#default_value' => array_keys($searchable_options),
                    '#delimiter' => '&nbsp;',
                );
            }
        }

        // Order options
        $search_order_options = array();
        if ($engine_features & Plugg_Search_Plugin::ENGINE_FEATURE_ORDER_BY_SCORE) {
            $search_order_options[Plugg_Search_Plugin::ORDER_SCORE] = $this->_('Score');
        }
        if ($engine_features & Plugg_Search_Plugin::ENGINE_FEATURE_ORDER_BY_DATE_DESC) {
            $search_order_options[Plugg_Search_Plugin::ORDER_DATE_DESC] = $this->_('Newest first');
        }
        if ($engine_features & Plugg_Search_Plugin::ENGINE_FEATURE_ORDER_BY_DATE_ASC) {
            $search_order_options[Plugg_Search_Plugin::ORDER_DATE_ASC] = $this->_('Oldest first');
        }
        if (!empty($search_order_options)) {
            $form['order'] = array(
                '#type' => 'radios',
                '#title' => $this->_('Order results by:'),
                '#options' => $search_order_options,
                '#default_value' => array_shift(array_keys($search_order_options)),
                '#delimiter' => '&nbsp;',
            );
        } else {
            $form['order'] = array(
                '#type' => 'hidden',
                '#value' => ''
            );
        }
        
        return $form;
    }
    
    public function validateForm(Plugg_Form_Form $form, Sabai_Request $request)
    {
        if (!isset($form->values['keyword']) || strlen($form->values['keyword']) === 0) {
            $form->setError($this->_('Please enter keywords to search for.'), 'keyword');
        } else {
            list($this->_keywords, $this->_keywordsFailed) = $this->_extractKeywords(
                $form->values['keyword'], $this->getPlugin()->getConfig('keywordMinLength')
            );
            if (empty($this->_keywords)) {
                if (!empty($this->_keywordsFailed)) {
                    $form->setError(
                        sprintf($this->_('Keywords must be more than %s characters.'), $this->getPlugin()->getConfig('keywordMinLength')),
                        'keyword'
                    );
                } else {
                    $form->setError($this->_('Please enter keywords to search for.'), 'keyword');
                }
            }
        }
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (isset($form->values['keyword_not']) && strlen($form->values['keyword_not'])) {
            list($keywords_not,) = $this->_extractKeywords($form->values['keyword_not']);
        } else {
            $keywords_not = array();
        }
        $engine = $this->getPlugin()->getEnginePlugin();
        $order = $request->asInt('order', null, array(
            Plugg_Search_Plugin::ORDER_DATE_ASC,
            Plugg_Search_Plugin::ORDER_DATE_DESC,
            Plugg_Search_Plugin::ORDER_SCORE
        ));
        $keywords_type = $request->asInt('keyword_type', Plugg_Search_Plugin::KEYWORDS_AND, array(
            Plugg_Search_Plugin::KEYWORDS_AND,
            Plugg_Search_Plugin::KEYWORDS_OR
        ));
        $perpage = $this->getPlugin()->getConfig('numResultsPage');

        // Create search page collection object
        $pages = new Plugg_Search_ResultPages($perpage, $engine, $this->_keywords, $keywords_type, $keywords_not, $order);
        if ($s_ = $request->asArray('s_')) {
            $pages->setSearchables($s_);
        } elseif ($p_ = $request->asArray('p_')) {
            $pages->setPlugins($p_);
        } else {
            $form->setConstantValues(array(
                's_' => isset($form->settings['s_']['#options']) ? array_keys($form->settings['s_']['#options']) : null,
                'p_' => isset($form->settings['p_']['#options']) ? array_keys($form->settings['p_']['#options']) : null, 
            ));
        }

        // Get valid search result page and its contents
        $page = $pages->getValidPage($request->asInt('p'));
        $results = $page->getElements();
        
        $vars = array(
            'search_pages' => $pages,
            'search_page' => $page,
            'search_keywords' => $this->_keywords,
            'search_keywords_failed' => $this->_keywordsFailed,
            'search_keywords_not' => $keywords_not,
            'search_keywords_type' => $keywords_type,
            'search_keywords_text' => $form->values['keyword'],
            'search_keywords_not_text' => @$form->values['keyword_not'],
            'search_order' => $order,
            'search_results' => $results,
            'search_has_score' => $engine->searchEngineGetFeatures() & Plugg_Search_Plugin::ENGINE_FEATURE_ORDER_BY_SCORE,
            'searchables' => $this->_searchables,
            'users' => $this->_fetchResultUserIdentities($results)
        );
        $this->_results = $this->RenderTemplate('search_main_index', $vars);
        
        // Always return false so that the search form is re-displayed
        return false;
    }
    
    public function viewForm(Plugg_Form_Form $form, &$formHtml, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (isset($this->_results)) {
            $formHtml = $formHtml . PHP_EOL . $this->_results;
        }
    }
    
    private function _extractKeywords($input, $minLength = 0)
    {
        $keywords = array();
        foreach ($this->Split($this->Trim($input)) as $keyword) {
            if ($quote_count = substr_count($keyword, '"')) { // check if any quotes
                $_keyword = explode('"', $keyword);
                if (isset($fragment)) { // has a phrase open but not closed?
                    $keywords[] = $fragment . ' ' . array_shift($_keyword);
                    unset($fragment);
                    if (!$quote_count % 2) {
                        // the last quote is not closed
                        $fragment .= array_pop($_keyword);
                    }
                } else {
                    if ($quote_count % 2) {
                        // the last quote is not closed
                        $fragment = array_pop($_keyword);
                    }
                }
                if (!empty($_keyword)) $keywords = array_merge($keywords, $_keyword);
            } else {
                if (isset($fragment)) { // has a phrase open but not closed?
                    $fragment .= ' ' . $keyword;
                } else {
                    $keywords[] = $keyword;
                }
            }
        }
        // Add the last unclosed fragment if any, to the list of keywords
        if (!empty($fragment)) $keywords[] = $fragment;

        // Extract unique keywords that are not empty
        $keywords_passed = $keywords_failed = array();
        foreach ($keywords as $keyword) {
            if (($keyword = trim($keyword)) && !isset($keywords_passed[$keyword]) && !isset($keywords_failed[$keyword])) {
                if (empty($minLength) || mb_strlen($keyword) >= $minLength) {
                    $keywords_passed[$keyword] = $keyword;
                } else {
                    $keywords_failed[$keyword] = $keyword;
                }
            }
        }
        
        return array($keywords_passed, $keywords_failed);
    }

    private function _fetchResultUserIdentities($results)
    {
        $author_ids = array();
        foreach ($results as $result) {
            if (!empty($result['author_id'])) {
                $author_ids[$result['author_id']] = null;
                $searchable_ids[$result['searchable_id']] = null;
            }
        }
        if (empty($author_ids)) return array();
        
        return $this->User_Identities(array_keys($author_ids));
    }
}
<?php
class Plugg_Search_Controller_Admin_System_Settings_Engine_ImportContent extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'contents' => array(
                '#title' => $this->_('Import content'),
                '#description' => $this->_('Here you can manually import contents to the search engine to make them searchable.'),
                '#type' => 'tableselect',
                '#multiple' => true,
                '#options' => array(),
                '#header' => array(
                    'title' => $this->_('Title'),
                    'body' => $this->_('Body'),
                    'author' => $this->_('Author'),
                    'created' => $this->_('Created'),
                    'modified' => $this->_('Modified'),
                ),
            ),
        );
        
        $searchable_plugin = $this->getPlugin($this->searchable->plugin);
        $pages = new Sabai_Page_Collection_Custom(
            array($searchable_plugin, 'searchCountContents'),
            array($searchable_plugin, 'searchFetchContents'),
            200,
            array(),
            array($this->searchable->name)
        );
        $page = $pages->getValidPage($request->asInt('p', 1));
        
        $content_ids = $user_ids = $current_content_ids = array();
        $contents = $page->getElements();
        
        // Get list of content and user ids
        foreach ($contents as $content) {
            $content_ids[] = $content['id'];
            $user_ids[] = $content['user_id'];
        }
        
        // Fetch users
        if (!empty($user_ids)) {
            $users = $this->User_Identities($user_ids);
        }
        
        // Fetch contents already registered on the search engine
        $engine_plugin = $this->getPlugin($this->engine->plugin);
        if (!empty($content_ids)) {
            $order = null;
            $current_contents = $engine_plugin->searchEngineListBySearchContentIds($this->searchable->id, $content_ids, $order);
        }

        // Add rows
        foreach ($contents as $content) {
            if (isset($current_contents[$content['id']])) {
                // Already imported
                $form['contents']['#options_disabled'][] = $content['id'];
                $form['contents']['#attributes'][$content['id']]['@row']['class'] = 'shadow'; // @all for whole row
            }
            $form['contents']['#options'][$content['id']] = array(
                'title' => $this->LinkTo(h($content['title']), array('base' => '/search', 'path' => sprintf('%d/%d', $this->searchable->id, $content['id']), 'script' => 'main')),
                'body' => h(mb_strimlength(strip_tags($content['body']), 0, 255)),
                'author' => (($user_id = $content['user_id']) && isset($users[$user_id])) ? $this->User_IdentityLink($users[$user_id]) : '',
                'created' => $this->DateTime($content['created']),
                'modified' => !empty($content['modified']) ? $this->DateTime($content['modified']) : '',
            );
        }
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['contents']['#footer'] = $this->PageNavRemote(
                'plugg-content',
                $pages,
                $page->getPageNumber(),
                $this->getUrl('/system/settings/search/' . $this->engine->id . '/' . $this->searchable->id)
            );
        }
        
        $this->_submitButtonLabel = $this->_('Import');

        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['contents'])) return true;
        
        // Register contents to search engine
        $count = 0;
        $contents = $this->getPlugin($this->searchable->plugin)
            ->searchFetchContentsByIds($this->searchable->name, $form->values['contents']);
        if ($contents->count() > 0) {
            $engine_plugin = $this->getPlugin($this->engine->plugin);
            foreach ($contents as $c) {
                if ($engine_plugin->searchEnginePut($this->searchable->plugin, $this->searchable->id, $c['id'], $c['title'],
                        $c['body'], $c['user_id'], $c['created'], $c['modified'], array(), $c['group'])
                ) {
                    ++$count;
                }
            }
        }
        $response->setSuccess(sprintf($this->_('%d content(s) imported successfully.'), $count), $request->getUrl());
        
        return true;
    }
}
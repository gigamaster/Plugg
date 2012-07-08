<?php
class Plugg_Search_Controller_Main_ViewContent extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        // Check if valid request
        if ((!$content_id = $request->asInt('content_id')) ||
            (!$searchable_id = $request->asInt('searchable_id'))
        ) {
            $response->setError($this->_('Invalid request'));

            return;
        }

        if ((!$searchable = $this->getPlugin()->getEntity($request, 'Searchable', $searchable_id))
            || (!$plugin = $this->getPlugin($searchable->plugin))
            || (!$url = $plugin->searchGetContentUrl($searchable->name, $content_id))
        ) {
            $this->_purgeContent($searchable_id, $content_id);
            $response->setError($this->_('Selected content no longer exists'));

            return;
        }

        // Redirect
        $response->setHeader('Location', $url)->send();
        exit;
    }

    private function _purgeContent($searchableId, $contentId)
    {
        $this->getPlugin()->getEnginePlugin()->searchEnginePurgeContent($searchableId, $contentId);
    }
}
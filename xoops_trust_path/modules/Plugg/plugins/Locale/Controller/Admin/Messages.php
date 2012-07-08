<?php
class Plugg_Locale_Controller_Admin_Messages extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $model = $this->getPluginModel();
        $token_param = Plugg::PARAM_TOKEN;
        $token_name = 'locale_messages_submit';
        $path = '/system/settings/locale/messages';

        // load custom messages
        $pages = $model->Message
            ->criteria()
            ->plugin_is('plugg')
            ->lang_is(SABAI_LANG)
            ->paginate(50);
        $page = $pages->getValidPage($request->asInt('p', 1));
        $custom = $custom_ids = array();
        foreach ($page->getElements() as $message) {
            $custom[$message->key] = $message->localized;
            $custom_ids[] = $message->id;
        }

        // load original messages
        $original = $this->_getOriginalMessages($request);

        // submit
        if ($request->isPost() && $this->getPlugin()->validateToken($token_param, $token_name, $request)) {
            if ($submitted = $request->asArray('messages')) {
                // delete retrieved custom messages first
                if (empty($custom_ids) || false !== $model->getGateway('Message')->deleteByIds($custom_ids)) {
                    if (false !== $saved = $this->getPlugin()->saveMessages($submitted, $original, 'plugg')) {
                        // cache messages
                        $this->getGettext()->updateCachedMessages(array_merge($original, $saved));
                        $response->setSuccess($this->_('Locale messages updated successfully'), $this->getUrl($path));
                        return;
                    }
                }
            }
        }
        
        $vars = array(
            'original_messages' => $original,
            'pages' => $pages,
            'custom_messages' => $custom,
            'token_param' => $token_param,
            'token_name' => $token_name,
            'submit_path' => $path
        );

        $response->setContent($this->RenderTemplate('locale_admin_messages', $vars))
            ->setPageInfo($this->_('Global message catalogue'));
    }

    function _getOriginalMessages(Sabai_Request $request)
    {
        // Reload messages without using cache
        $this->getGettext()->loadMessages($this->getId(), 'Plugg.mo', false);
        return $this->getGettext()->getMessages($this->getId());
    }
}
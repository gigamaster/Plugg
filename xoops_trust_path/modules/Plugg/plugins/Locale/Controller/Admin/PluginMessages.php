<?php
class Plugg_Locale_Controller_Admin_PluginMessages extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $model = $this->getPluginModel();
        $token_param = Plugg::PARAM_TOKEN;
        $token_name = 'locale_messages_submit';
        $plugin = $this->getPlugin($request->asStr('plugin_name'));
        $path = '/system/settings/locale/messages/' . $plugin->name;

        // load custom messages
        $pages = $model->Message
            ->criteria()
            ->plugin_is($plugin->name)
            ->lang_is(SABAI_LANG)
            ->paginate(50);
        $page = $pages->getValidPage($request->asInt('p', 1));
        $custom = $custom_ids = array();
        foreach ($page->getElements() as $message) {
            $custom[$message->key] = $message->localized;
            $custom_ids[] = $message->id;
        }

        // load original messages
        $original = $this->_getPluginOriginalMessages($plugin);

        // submit
        if ($request->isPost() && $this->getPlugin()->validateToken($token_param, $token_name, $request)) {
            if ($submitted = $request->asArray('messages')) {
                // delete retrieved custom messages first
                if (empty($custom_ids) || false !== $model->getGateway('Message')->deleteByIds($custom_ids)) {
                    if (false !== $saved = $this->getPlugin()->saveMessages($submitted, $original, $plugin->name)) {
                        // cache messages
                        $this->getGettext()->updateCachedMessages(array_merge($original, $saved), $plugin->name);
                        $response->setSuccess($this->_('Locale messages updated successfully'), $this->getUrl($path));

                        return;
                    }
                }
            }
        }

        $page_title = sprintf(
            '%s - %s',
            $plugin->nicename,
            $plugin->name
        );
        $vars = array(
            'original_messages' => $original,
            'pages' => $pages,
            'custom_messages' => $custom,
            'token_param' => $token_param,
            'token_name' => $token_name,
            'submit_path' => $path
        );

        $response->setContent($this->RenderTemplate('locale_admin_messages', $vars))
            ->setPageInfo($page_title);
    }

    function _getPluginOriginalMessages($plugin)
    {
        // Reload messages without using cache
        $this->getGettext()->loadMessages($plugin->name, $plugin->name . '.mo', false);

        return $this->getGettext()->getMessages($plugin->name);
    }
}
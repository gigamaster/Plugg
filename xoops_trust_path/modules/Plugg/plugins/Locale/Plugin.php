<?php
class Plugg_Locale_Plugin extends Plugg_Plugin implements Plugg_System_Routable_Admin
{
    /* Start implementation of Plugg_System_Routable_Admin */

    public function systemRoutableGetAdminRoutes()
    {
        return array(
            '/system/settings/locale' => array(
                'controller' => 'Index',
                'title' => $this->_nicename,
                'type' => Plugg::ROUTE_TAB,
            ),
            '/system/settings/locale/messages' => array(
                'controller' => 'Messages',
            ),
            '/system/settings/locale/messages/:plugin_name' => array(
                'controller' => 'PluginMessages',
                'access_callback' => true,
            )
        );
    }

    public function systemRoutableOnAdminAccess(Sabai_Request $request, Sabai_Application_Response $response, $path)
    {
        switch ($path) {
            case '/system/settings/locale/messages/:plugin_name':
                return ($plugin_name = $request->asStr('plugin_name'))
                    && ($plugin = $this->_application->getPlugin($plugin_name, false))
                    && $plugin->hasLocale();
        }

    }

    public function systemRoutableOnAdminTitle(Sabai_Request $request, Sabai_Application_Response $response, $path, $title, $titleType){}

    /* End implementation of Plugg_System_Routable_Admin */

    function onSystemAdminPluginConfigured($plugin)
    {
        $this->updatePluginGettextMessages($plugin->name);
    }

    function onSystemAdminPluginUpgraded($plugin)
    {
        $this->updatePluginGettextMessages($plugin->name);
    }

    function updatePluginGettextMessages($pluginName)
    {
        $messages = $this->getModel()->Message
            ->criteria()
            ->plugin_is($pluginName)
            ->lang_is(SABAI_LANG)
            ->fetch();
        $custom = array();
        foreach ($messages as $message) {
            $custom[$message->key] = $message->localized;
        }
        if (!empty($custom)) {
            $original = $this->_application->getGettext()->getMessages($pluginName);
            $this->_application->getGettext()->updateCachedMessages(array_merge($original, $custom), $pluginName);
        }
    }

    public function saveMessages($submitted, $original, $pluginName, $lang = SABAI_LANG)
    {
        $model = $this->getModel();
        $messages = array_filter(array_intersect_key($submitted, $original), array($this, '_filterMessage'));
        foreach (array_keys($messages) as $k) {
            $message = $model->create('Message');
            $message->key = $k;
            $message->localized = $messages[$k];
            $message->lang = $lang;
            $message->plugin = $pluginName;
            $message->markNew();
        }

        return $model->commit() ? $messages : false;
    }

    private function _filterMessage($message)
    {
        return $message != '';
    }

    public function validateToken($tokenParam, $tokenName, Sabai_Request $request)
    {
        if (!$token_value = $request->asStr($tokenParam, false)) return false;

        if (!Sabai_Token::validate($token_value, $tokenName)) return false;

        return true;
    }
}
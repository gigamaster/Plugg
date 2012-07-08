<?php
class Plugg_jQuery_Plugin extends Plugg_Plugin
{
    public function onMainControllerEnter(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->_onPluggEnter($request, $response);
    }

    public function onAdminControllerEnter(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->_onPluggEnter($request, $response);
    }

    private function _onPluggEnter(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $response->addJsFile($this->_application->JsUrl($this->_name, 'jquery-1.4.2.min.js'))
            ->addJsFile($this->_application->JsUrl($this->_name, 'json2.js'))
            ->addJsFile($this->_application->JsUrl($this->_name, 'jquery.scrollTo-min.js'))
            ->addJsFile($this->_application->JsUrl($this->_name, 'jquery-ui-1.8.custom.min.js'))
            ->addJsFile($this->_application->JsUrl($this->_name, 'jquery.bgiframe.min.js'), false, 'IE 6')
            ->addJsFile($this->_application->JsUrl($this->_name, 'jquery.flash.js'))
            ->addJsFile($this->_application->JsUrl($this->_name, 'jquery.scrollfollow.js'))
            ->addJsFile($this->_application->JsUrl($this->_name, 'jquery.colorbox-min.js'))
            ->addJsFile($this->_application->JsUrl($this->_name, 'jquery.plugg.js'))
            ->addJsHead('jQuery.noConflict();')
            ->addJs($js = sprintf('jQuery.plugg.init(); jQuery("a.colorbox").colorbox({current: "%s"});', $this->_('{current} of {total}')))
            ->addJsHeadAjax($js);
    }
}
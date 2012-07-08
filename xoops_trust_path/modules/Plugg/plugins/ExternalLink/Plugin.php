<?php
class Plugg_ExternalLink_Plugin extends Plugg_Plugin
{
    function onMainControllerEnter($request, Sabai_Application_Response $response)
    {
        $this->_onPluggEnter($request, $response);
    }

    function onAdminControllerEnter($request, Sabai_Application_Response $response)
    {
        $this->_onPluggEnter($request, $response, true);
    }

    function _onPluggEnter(Sabai_Request $request, Sabai_Application_Response $response, $admin = false)
    {
        if (!$localhost = $this->getConfig('localhost')) return;
        
        if ($this->getConfig('popup')) {
            $popup_js = 'jQuery(this).attr("target", "_blank").colorbox({iframe:true, width:"80%", height:"80%"});';
        } else {
            $popup_js = '';
        }

        $js = sprintf(
            "jQuery('.plugg a[href^=\"http\"]:not([href*=\"%2\$s\"]):not(:has(img))').css({
  background: \"url('%1\$s') no-repeat right top\",
  paddingRight: \"12px\"
});
jQuery('.plugg a[href^=\"http\"]:not([href*=\"%2\$s\"])').each(function(){
    %3\$s%4\$s
});
",
            $this->_application->ImageUrl($this->_name, 'external_link.gif', '', '&'),
            $localhost,
            $popup_js,
            $this->getConfig('nofollow') ? 'jQuery(this).attr("rel", "nofollow");' : ''
        );
        $response->addJs($js)->addJsHeadAjax($js);
    }

    public function onFormBuildSystemAdminSettingsSeo($form)
    {
        $form[$this->_name] = array(
            '#title' => $this->_('External link settings'),
            '#collapsible' => true,
            '#tree' => true,
            'localhost' => array(
                '#type' => 'textfield',
                '#title' => $this->_('Local host name'),
                '#description' => $this->_('Links that include the following text as the host part of URL will not be considered as an external link.'),
                '#default_value' => $this->getConfig('localhost'),
                '#size' => 50,
            ),
            'popup' => array(
                '#type' => 'checkbox',
                '#default_value' => $this->getConfig('popup'),
                '#title' => $this->_('Open external links in a popup window.'),
                '#description' => $this->_('Check this option to always display external links in a new window.'),
            ),
            'nofollow' => array(
                '#type' => 'checkbox',
                '#default_value' => $this->getConfig('nofollow'),
                '#title' => $this->_('Add "rel=nofollow" to external links.'),
                '#description' => $this->_('Chick this option to automatically add "rel=nofollow" to all external links.'),
            )
        );

        // Add callback called upon sumission of the form
        $form['#submit'][] = array($this, 'submitSystemAdminSettingsSeo');
    }

    public function submitSystemAdminSettingsSeo($form)
    {
        if (!empty($form->values[$this->_name])) {
            $this->saveConfig($form->values[$this->_name], array(), false);
        }
    }
    
    public function getDefaultConfig()
    {
        return array(
            'localhost' => $_SERVER['HTTP_HOST'],
            'popup' => true,
            'nofollow' => true,
        );
    }
}
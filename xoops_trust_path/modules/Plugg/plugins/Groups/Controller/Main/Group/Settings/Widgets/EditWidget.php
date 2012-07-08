<?php
class Plugg_Groups_Controller_Main_Group_Settings_Widgets_EditWidget extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        if (!$widget_plugin = $this->getPlugin($this->widget->plugin)) {
            return false;
        }
        $this->activewidget = $this->_getActiveWidget($request);

        $this->_submitButtonLabel = $this->_('Save configuration');
        $this->_ajaxOnSuccessRedirect = false;
        $this->_ajaxOnSuccess = 'function(xhr, result, target){target.slideUp("fast"); target.closest("li").find("a.toggleProcessed").hide().end().find("a.toggle").show();}';
        $this->_ajaxCancelType = 'slide';
        $form = array(
            '#id' => sprintf('%s_%s_%s', 'groups_group_manage_editwidget', $this->widget->plugin, $this->widget->name),
            '#action' => $this->createUrl(array('path' => 'settings/widgets/' . $this->widget->id . '/edit')),
            '#token' => array('reuse' => true),
            'title' => array(
                '#title' => $this->_('Custom title'),
                '#default_value' => $widget_plugin->groupsWidgetGetTitle($this->widget->name)
            ),

        );
        if ($this->widget->isCacheable()) {
            $form['cache_lifetime'] = array(
                '#type' => 'select',
                '#title' => $this->_('Cache lifetime'),
                '#default_value' => 3600,
                '#options' => array(
                    0 => $this->_('No cache'),
                    60 => $this->_('1 minute'),
                    180 => sprintf($this->_('%d minutes'), 3),
                    300 => sprintf($this->_('%d minutes'), 5),
                    900 => sprintf($this->_('%d minutes'), 15),
                    1800 => sprintf($this->_('%d minutes'), 30),
                    3600 => $this->_('1 hour'),
                    10800 => sprintf($this->_('%d hours'), 3),
                    18000 => sprintf($this->_('%d hours'), 5),
                    86400 => $this->_('1 day'),
                    604800 => sprintf($this->_('%d days'), 7),
                    2592000 => sprintf($this->_('%d days'), 30),
                )
            );
        }

        // Set current values if active widget already exists
        $current_settings = array();
        if ($this->activewidget->id) {
            $form['title']['#default_value'] = $this->activewidget->title;
            if (isset($form['cache_lifetime'])) {
                $form['cache_lifetime']['#default_value'] = $this->activewidget->cache_lifetime;
            }
            $current_settings = unserialize($this->activewidget->settings);
        }
        $form['settings'] = array_merge(
            $widget_plugin->groupsWidgetGetSettings($this->widget->name, $current_settings),
            array(
                '#type' => 'fieldset',
                '#tree' => true,
            )
        );

        $response->setPageInfo($form['title']['#default_value']);

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        $this->activewidget->title = $form->values['title'];
        $this->activewidget->cache_lifetime = isset($form->values['cache_lifetime']) ? $form->values['cache_lifetime'] : 0;
        $this->activewidget->settings = serialize(isset($form->values['settings']) ? $form->values['settings'] : array());
        // Clear cache
        $this->activewidget->cache = '';
        $this->activewidget->cache_time = 0;

        return $this->activewidget->commit() ? true : false;
    }

    private function _getActiveWidget($request)
    {
        $ret = $this->getPluginModel()->Activewidget
            ->criteria()
            ->groupId_is($this->group->id)
            ->fetchByWidget($this->widget->id)
            ->getFirst();
        if (!$ret) {
            $ret = $this->widget->createActivewidget();
            $ret->assignGroup($this->group);
            $ret->markNew();
        }

        return $ret;
    }
}
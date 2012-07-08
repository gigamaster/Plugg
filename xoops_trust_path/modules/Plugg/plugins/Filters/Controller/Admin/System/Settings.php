<?php
class Plugg_Filters_Controller_Admin_System_Settings extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'filters' => array(
                '#type' => 'grid',
                '#sortable' => true,
                'title' => array(
                    '#type' => 'textfield',
                    '#title' => $this->_('Name'),
                    '#size' => 25
                ),
                'plugin' =>  array(
                    '#type' => 'item',
                    '#title' => $this->_('Plugin'),
                ),
                'active' =>  array(
                    '#type' => 'checkbox',
                    '#title' => $this->_('Active'),
                ),
                'links' => array(
                    '#type' => 'item',
                    '#title' => '',
                ),
                '#default_value' => array(),
            )
        );
        
        // Add rows
        $filters = $this->getPluginModel()->Filter->fetch(0, 0, 'order', 'ASC');
        foreach ($filters as $filter) {
            if ((!$filter_plugin = $this->getPlugin($filter->plugin))
             || !$filter_plugin instanceof Plugg_Filters_Filter
            ) {
                continue; // not a valid filter plugin
            }
            $filter_path = '/system/settings/filters/' . $filter->id;
            $links = array();
            if ($filter_plugin->filtersFilterGetSettings($filter->name)) {
                $links[] = $this->LinkToRemote(
                    $this->_('Configure'), 'plugg-content', $this->getUrl($filter_path . '/configure')
                );
            }
            $form['filters']['#default_value'][$filter->id] = array(
                'title' => $filter->title,
                'plugin' => h(sprintf('%s - %s', $filter_plugin->nicename, $filter->name)),
                'active' => $filter->active,
                'links' => implode(PHP_EOL, $links),
            );
        }
        
        $this->_cancelUrl = null;
        $this->_submitButtonLabel = $this->_('Save configuration');
        
        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['filters'])) return true;
        
        $filter_order = $filter_active = $filter_title = array();
        $order = 1;
        foreach ($form->values['filters'] as $filter_id => $filter) {
            $filter_order[$filter_id] = $order;
            $filter_active[$filter_id] = !empty($filter['active']);
            $filter_title[$filter_id] = $filter['title'];
            ++$order;
        }
        
        $model = $this->getPluginModel();
        $filters = $model->Filter->criteria()->id_in(array_keys($filter_order))->fetch();
        foreach ($filters as $filter) {
            $filter->order = $filter_order[$filter->id];
            $filter->active = $filter_active[$filter->id];
            $filter->title = $filter_title[$filter->id];
        }
        
        $model->commit();
    }
}
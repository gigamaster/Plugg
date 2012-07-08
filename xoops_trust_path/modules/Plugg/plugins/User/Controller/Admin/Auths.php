<?php
class Plugg_User_Controller_Admin_Auths extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'auths' => array(
                '#type' => 'grid',
                '#sortable' => true,
                'id' =>  array(
                    '#type' => 'hidden',
                    '#title' => '',
                ),
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
                'data' => array(
                    '#type' => 'item',
                    '#title' => $this->_('Data'),
                ),
                'links' => array(
                    '#type' => 'item',
                    '#title' => '',
                ),
                '#default_value' => array(),
            )
        );
        
        // Add rows
        $auths = $this->getPluginModel()->Auth->fetch(0, 0, 'order', 'ASC');
        foreach ($auths as $auth) {
            if ((!$auth_plugin = $this->getPlugin($auth->plugin))
             || !$auth_plugin instanceof Plugg_User_Authenticator
            ) {
                continue; // not a valid auth plugin
            }
            $auth_path = '/user/auths/' . $auth->id;
            $links = array($this->LinkToRemote($this->_('Details'), 'plugg-content', $this->getUrl($auth_path)));
            if ($auth_plugin->userAuthGetSettings($auth->name)) {
                $links[] = $this->LinkToRemote(
                    $this->_('Configure'), 'plugg-content', $this->getUrl($auth_path . '/configure')
                );
            }
            $form['auths']['#default_value'][] = array(
                'id' => $auth->id,
                'title' => $auth->title,
                'plugin' => $auth_plugin->nicename,
                'active' => $auth->active,
                'data' => $auth->authdata_count,
                'links' => implode(PHP_EOL, $links),
            );
        }
        
        $this->_submitButtonLabel = $this->_('Save configuration');
        $this->_cancelUrl = null;
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['auths'])) return true;
        
        $auth_order = $auth_active = $auth_title = array();
        foreach (array_merge(array(), $form->values['auths']) as $order => $auth) {
            $auth_order[$auth['id']] = $order;
            $auth_active[$auth['id']] = !empty($auth['active']);
            $auth_title[$auth['id']] = $auth['title'];
        }
        
        $model = $this->getPluginModel();
        $auths = $model->Auth->criteria()->id_in(array_keys($auth_order))->fetch();
        foreach ($auths as $auth) {
            $auth->order = $auth_order[$auth->id];
            $auth->active = $auth_active[$auth->id];
            $auth->title = $auth_title[$auth->id];
        }
        
        return $model->commit();
    }
}
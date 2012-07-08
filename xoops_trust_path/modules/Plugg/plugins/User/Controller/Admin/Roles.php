<?php
class Plugg_User_Controller_Admin_Roles extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'roles' => array(
                '#type' => 'tableselect',
                '#header' => array(),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $headers = array(
            'name' => $this->_('Name'),
            'created' => $this->_('Created'),
            'members' => $this->_('Members'),
        );
        $sort = $request->asStr('sort', 'name', array_keys($headers));
        $order = $request->asStr('order', 'ASC', array('ASC', 'DESC'));
        $path = '/user/roles';
        
        // Add headers
        foreach ($headers as $header_name => $header_label) {
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['roles']['#header'][$header_name] = $this->LinkToRemote(
                $header_label, 'plugg-content', $this->getUrl($path, $url_params), array(), array(), $attr
            );
        }
        $form['roles']['#header']['links'] = '';
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Role->paginate(20, array($sort), array($order));
        $roles = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($roles as $role) {
            $role_path = $path . '/' . $role->id;
            $links = array(
                $this->LinkToRemote($this->_('Details'), 'plugg-content', $this->getUrl($role_path)),
                $this->LinkToRemote($this->_('Edit'), 'plugg-content', $this->getUrl($role_path . '/edit')),
            );
            if ($role->system) {
                $form['roles']['#options_disabled'][] = $role->id;
            }
            $form['roles']['#options'][$role->id] = array(
                'name' => h($role->display_name),
                'created' => $this->DateTime($role->created),
                'members' => $role->member_count,
                'links' => implode(PHP_EOL, $links),
            );
        }
        
        $this->_submitButtonLabel = $this->_('Delete selected');
        $this->_cancelUrl = null;
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['roles']['#footer'] = $this->PageNavRemote(
                'plugg-content', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['roles'])) return true;
        
        $model = $this->getPluginModel();
        $roles = $model->Role->criteria()->system_is(0)->id_in($form->values['roles'])->fetch();
        foreach ($roles as $role) {
            $role->markRemoved();
        }
        
        return $model->commit();
    }
}
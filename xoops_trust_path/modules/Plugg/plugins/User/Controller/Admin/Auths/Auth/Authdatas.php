<?php
class Plugg_User_Controller_Admin_Auths_Auth_Authdatas extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'authdatas' => array(
                '#type' => 'tableselect',
                '#header' => array('user' => '', 'username' => $this->_('User')),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
                '#title' => $this->_('Accounts'),
            )
        );
        
        $sortable_headers = array(
            'display_id' => $this->_('Auth ID'),
            'created' => $this->_('Created'),
            'lastused' => $this->_('Last used'),
        );
        $sort = $request->asStr('sort', 'lastused', array_keys($sortable_headers));
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        $path = '/user/auths/' . $this->auth->id . '/authdatas';
        
        // Add headers
        foreach ($sortable_headers as $header_name => $header_label) {
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['authdatas']['#header'][$header_name] = $this->LinkToRemote(
                $header_label,
                'plugg-user-admin-auths-auth-authdatas',
                $this->getUrl($path, $url_params),
                array(),
                array(),
                $attr
            );
        }
        
        // Add rows (options)
        $pages = $this->auth->paginateAuthdatas(20, $sort, $order);
        $authdatas = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($authdatas->with('User') as $authdata) {
            $form['authdatas']['#options'][$authdata->id] = array(
                'user' => $this->User_IdentityIcon($authdata->User),
                'username' => $this->User_IdentityLink($authdata->User),
                'display_id' => h($authdata->display_id),
                'created' => $this->DateTime($authdata->created),
                'lastused' => $this->DateTime($authdata->lastused),
            );
        }
        
        $this->_submitButtonLabel = $this->_('Delete selected');
        $this->_successUrl = $this->getUrl('/user/auths/' . $this->auth->id);
        $this->_cancelUrl = null;
        $this->_ajaxCancelType = 'none';
        $this->_ajaxOnSuccessUrl = $this->getUrl($path);
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['authdatas']['#footer'] = $this->PageNavRemote(
                'plugg-user-admin-auths-auth-authdatas', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['authdatas'])) return true;
        
        $model = $this->getPluginModel();
        $authdatas = $model->Authdata->criteria()->authId_is($this->auth->id)->id_in($form->values['authdatas'])->fetch();
        foreach ($authdatas as $authdata) {
            $authdata->markRemoved();
        }
        
        return $model->commit();
    }
}
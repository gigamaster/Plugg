<?php
class Plugg_User_Controller_Admin_Roles_Role_Members extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'members' => array(
                '#type' => 'tableselect',
                '#header' => array('user' => '', 'username' => $this->_('User')),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $sortable_headers = array(
            'created' => $this->_('Date assigned role'),
        );
        $sort = $request->asStr('sort', 'created', array_keys($sortable_headers));
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        $path = '/user/roles/' . $this->role->id . '/members';
        
        // Add headers
        foreach ($sortable_headers as $header_name => $header_label) {
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['members']['#header'][$header_name] = $this->LinkToRemote(
                $header_label,
                'plugg-user-admin-roles-role-members',
                $this->getUrl($path, $url_params),
                array(),
                array(),
                $attr
            );
        }
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Member->criteria()
            ->roleId_is($this->role->id)
            ->paginate(20, $sort, $order);
        $members = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($members->with('User') as $member) {
            $form['members']['#options'][$member->id] = array(
                'user' => $this->User_IdentityIcon($member->User),
                'username' => $this->User_IdentityLink($member->User),
                'created' => $this->DateTime($member->created),
            );
        }
        
        $this->_submitButtonLabel = $this->_('Remove selected');
        $this->_successUrl = $this->getUrl('/user/roles/' . $this->role->id);
        $this->_ajaxCancelType = 'none';
        $this->_ajaxOnSuccessUrl = $this->getUrl($path);
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['members']['#footer'] = $this->PageNavRemote(
                'plugg-user-admin-roles-role-members', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['members'])) return true;
        
        $model = $this->getPluginModel();
        foreach ($model->Member->criteria()->id_in($form->values['members'])->fetch() as $member) {
            $member->markRemoved();
        }
        
        return $model->commit();
    }
}
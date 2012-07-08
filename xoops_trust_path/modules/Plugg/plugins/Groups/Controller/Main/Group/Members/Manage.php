<?php
class Plugg_Groups_Controller_Main_Group_Members_Manage extends Plugg_Form_Controller
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
            'role' => $this->_('Group role'),
            'updated' => $this->_('Date joined'),
        );
        $sort = $request->asStr('sort', 'updated', array_keys($sortable_headers));
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        $path = '/groups/' . $this->group->name . '/members/manage';
        
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
                'plugg-content',
                $this->getUrl($path, $url_params),
                array(),
                array(),
                $attr
            );
        }
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Member->criteria()
            ->groupId_is($this->group->id)
            ->status_is(Plugg_Groups_Plugin::MEMBER_STATUS_ACTIVE)
            ->paginate(20, $sort, $order);
        $members = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($members->with('User') as $member) {
            $form['members']['#options'][$member->id] = array(
                'user' => $this->User_IdentityIcon($member->User),
                'username' => $this->User_IdentityLink($member->User),
                'updated' => $this->DateTime($member->updated ? $member->updated : $member->created),
                'role' => $member->isAdmin() ? $this->_('Administrator') : '',
            );
        }
        
        $this->_submitButtonLabel = $this->_('Remove membership');
        $this->_successUrl = $this->group->getUrl('members/manage');
        $this->_ajaxCancelType = 'none';
        $this->_ajaxOnSuccessUrl = $this->getUrl($path);
        
        $form[$this->_submitButtonName]['assign_admin'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Assign administrator role'),
            '#submit' => array(array(array($this, 'assignAdministrator'), array($request))),
        );
        
        $form[$this->_submitButtonName]['revoke_admin'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Revoke administrator role'),
            '#submit' => array(array(array($this, 'revokeAdministrator'), array($request))),
        );
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['members']['#footer'] = $this->PageNavRemote(
                'plugg-groups-main-group-members-manage', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['members'])) return true;
        
        $model = $this->getPluginModel();
        $members = $model->Member->criteria()
            ->groupId_is($this->group->id)
            ->id_in($form->values['members'])
            ->fetch();
        foreach ($members as $member) {
            if ($member->isAdmin() && $member->isOwnedBy($this->getUser())) {
                $form->setError($this->_('You may not remove yourself from the group while you are a group administrator.'));
                
                return false;
            }
            
            $member->markRemoved();
        }
        
        return $model->commit();
    }
    
    public function assignAdministrator(Plugg_Form_Form $form, Sabai_Request $request)
    {
        if (empty($form->values['members'])) return true;
        
        $model = $this->getPluginModel();
        $members = $model->Member->criteria()
            ->role_isNot(Plugg_Groups_Plugin::MEMBER_ROLE_ADMINISTRATOR)
            ->id_in($form->values['members'])
            ->groupId_is($this->group->id)
            ->fetch();
        foreach ($members as $member) {
            $member->role = Plugg_Groups_Plugin::MEMBER_ROLE_ADMINISTRATOR;
        }
        
        return $model->commit();
    }
    
    public function revokeAdministrator(Plugg_Form_Form $form, Sabai_Request $request)
    {
        if (empty($form->values['members'])) return true;
        
        $model = $this->getPluginModel();
        $members = $model->Member->criteria()
            ->role_is(Plugg_Groups_Plugin::MEMBER_ROLE_ADMINISTRATOR)
            ->id_in($form->values['members'])
            ->groupId_is($this->group->id)
            ->fetch();
        foreach ($members as $member) {
            if ($member->isOwnedBy($this->getUser())) {
                $form->setError($this->_('You may not revoke the administrator role from yourself.'));
                
                return false;
            }
            
            $member->role = Plugg_Groups_Plugin::MEMBER_ROLE_NONE;
        }
        
        return $model->commit();
    }
}
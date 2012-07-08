<?php
class Plugg_User_Controller_Admin_Index extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $form = array(
            'users' => array(
                '#type' => 'tableselect',
                '#header' => array('user' => ''),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $sortable_headers = array(
            'id' => $this->_('ID'),
            'username' => $this->_('Username'),
            'created' => $this->_('Date created'),
        );
        $sort = $request->asStr('sort', 'created', array_keys($sortable_headers));
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        $path = '/user';
        
        // Add headers
        foreach ($sortable_headers as $header_name => $header_label) {
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['users']['#header'][$header_name] = $this->LinkToRemote(
                $header_label,
                'plugg-content',
                $this->getUrl($path, $url_params),
                array(),
                array(),
                $attr
            );
        }
        $form['users']['#header']['roles'] = $this->_('Roles');
        $form['users']['#header']['links'] = '';
        
        // Add rows (options)
        $pages = $this->getPlugin()->getIdentityFetcher()->paginateIdentities(20, $sort, $order);
        $page = $pages->getValidPage($request->asInt('p', 1));
        $users = $page->getElements();
        foreach ($users as $user) $users_arr[$user->id] = $user;
        if (!empty($users_arr)) {
            $model = $this->getPluginModel();
            foreach ($model->Role->fetch() as $role) {
                $roles_arr[$role->id] = $role;
                $role_options[$role->id] = $role->display_name;
            }
            foreach ($model->Member->fetchByUser(array_keys($users_arr)) as $member) {
                $user_roles[$member->user_id][] = $member->role_id;
            }
            foreach ($users as $user) {
                $user_role_links = array();
                if (!empty($user_roles[$user->id])) {
                    foreach ($user_roles[$user->id] as $user_role_id) {
                        if (!$role = @$roles_arr[$user_role_id]) continue;
                        $user_role_links[] = $this->LinkToRemote(h($role->display_name), 'plugg-content', $this->getUrl('/user/roles/' . $role->id));
                    }
                }
                $form['users']['#options'][$user->id] = array(
                    'user' => $this->User_IdentityIcon($user),
                    'id' => $user->id,
                    'username' => $this->User_IdentityLink($user),
                    'created' => $this->DateTime($user->created),
                    'roles' => implode(', ', $user_role_links),
                    'links' => implode(PHP_EOL, array(
                        $this->LinkTo($this->_('Edit'), $this->User_IdentityUrl($user, 'edit')),
                        $this->LinkTo($this->_('Delete'), $this->User_IdentityUrl($user, 'settings/delete')),
                    ))
                );
            }
        }
        
        $this->_submitButtonLabel = $this->_('Assign');
        $this->_successUrl = $this->getUrl('/user');
        $this->_cancelUrl = null;

        if (!empty($role_options)) {
            $form[$this->_submitButtonName]['role'] = array(
                '#weight' => -1,
                '#type' => 'select',
                '#options' => $role_options,
                '#field_prefix' => $this->_('Role:'),
                '#template' => false,
                '#tree' => false,
            );
        }
        
        $form[$this->_submitButtonName]['revoke'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Revoke'),
            '#submit' => array(array(array($this, 'revokeRole'), array($request))),
            '#weight' => 1,
        );
        
        if ($pages->count() > 1) {
            $form['users']['#footer'] = $this->PageNavRemote(
                'plugg-content', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['users'])) return true;
        
        $model = $this->getPluginModel();
        if (!$role = $model->Role->fetchById($form->values['role'])) {
            $form->setError($this->_('The selected role does not exist.'));
            
            return;
        }
        
        $members = $model->Member->criteria()
            ->userId_in($form->values['users'])
            ->fetchByRole($form->values['role']);
        foreach ($members as $member) {
            $current_members[$member->user_id] = $member->id;
        }
        foreach ($form->values['users'] as $user_id) {
            if (isset($current_members[$user_id])) continue; // already assigned
            
            $new_member = $role->createMember();
            $new_member->user_id = $user_id;
            $new_member->markNew();
        }
        
        return $model->commit();
    }
    
    public function revokeRole(Plugg_Form_Form $form, Sabai_Request $request)
    {
        if (empty($form->values['users'])) return true;
        
        $model = $this->getPluginModel();
        
        if (!$role = $model->Role->fetchById($form->values['role'])) {
            $form->setError($this->_('The selected role does not exist.'));
            
            return;
        }
        
        $members = $model->Member->criteria()
            ->userId_in($form->values['users'])
            ->fetchByRole($role->id);
        foreach ($members as $member) {
            $member->markRemoved();
        }
        
        return $model->commit();
    }
}
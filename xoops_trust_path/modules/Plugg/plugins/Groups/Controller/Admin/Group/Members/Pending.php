<?php
class Plugg_Groups_Controller_Admin_Group_Members_Pending extends Plugg_Form_Controller
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
            'updated' => $this->_('Date requested'),
        );
        $sort = $request->asStr('sort', 'updated', array_keys($sortable_headers));
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        $path = '/groups/' . $this->group->id . '/members/pending';
        
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
                'plugg-groups-members-pending',
                $this->getUrl($path, $url_params),
                array(),
                array(),
                $attr
            );
        }
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Member->criteria()
            ->groupId_is($this->group->id)
            ->status_is(Plugg_Groups_Plugin::MEMBER_STATUS_PENDING)
            ->paginate(20, $sort, $order);
        $members = $pages->getValidPage($request->asInt('p', 1))->getElements();
        foreach ($members->with('User') as $member) {
            $form['members']['#options'][$member->id] = array(
                'user' => $this->User_IdentityIcon($member->User),
                'username' => $this->User_IdentityLink($member->User),
                'updated' => $this->DateTime($member->updated ? $member->updated : $member->created),
            );
        }
        
        $this->_submitButtonLabel = $this->_('Approve selected');
        $this->_successUrl = $this->getUrl('/groups/' . $this->group->id);
        $this->_ajaxCancelType = 'none';
        $this->_ajaxOnSuccessUrl = $this->getUrl($path);
        
        $form[$this->_submitButtonName]['reject'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Reject selected'),
            '#submit' => array(array($this, 'reject')),
            '#weight' => 1,
        );
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['members']['#footer'] = $this->PageNavRemote(
                'plugg-groups-members-pending', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['members'])) return true;
        
        $model = $this->getPluginModel();
        $members = $model->Member->criteria()
            ->status_is(Plugg_Groups_Plugin::MEMBER_STATUS_PENDING)
            ->id_in($form->values['members'])
            ->fetch();
        foreach ($members as $member) {
            $member->setActive();
        }
        
        if ($ret = $model->commit()) {
            foreach ($members->with('User') as $member) {
                $this->getPlugin()->sendJoinGroupRequestApprovedEmail($this->group, $member->User);
            }
        }

        return $ret;
    }
    
    public function reject(Plugg_Form_Form $form)
    {
        if (empty($form->values['members'])) return true;
        
        $model = $this->getPluginModel();
        $members = $model->Member->criteria()
            ->status_is(Plugg_Groups_Plugin::MEMBER_STATUS_PENDING)
            ->id_in($form->values['members'])
            ->fetch();
        foreach ($members as $member) {
            $member->markRemoved();
        }
        
        return $model->commit();
    }
}
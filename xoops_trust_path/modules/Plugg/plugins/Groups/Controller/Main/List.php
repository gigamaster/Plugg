<?php
class Plugg_Groups_Controller_Main_List extends Plugg_Form_Controller
{
    private $_actions = array(), $_groups = array(), $_groupViews = array(), $_groupStars = array();
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $path = '/groups/list';
        $this->_submitable = false;
        $this->_submitButtonName = null;
        $this->_cancelUrl = null;
        
        $form = array(
            'groups' => array(
                '#type' => 'tableselect',
                '#header' => array(),
                '#options' => array(),
                '#disabled' => true,
            )
        );
        
        $headers = array(
            'icon' => '',
            'name' => $this->_('Group name'),
            'created' => $this->_('Date created'),
            'type' => $this->_('Type'),
            'member_count' => $this->_('Members'),
            'member_lasttime' => $this->_('Latest member'),
            'links' => '',
        );
        
        $sortable_headers = array('name', 'created', 'type', 'member_count', 'member_lasttime');
        $sort = $request->asStr('sort', 'created', array_keys($sortable_headers));
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        
        // Add headers
        foreach ($headers as $header_name => $header_label) {
            if (!in_array($header_name, $sortable_headers)) {
                $form['groups']['#header'][$header_name] = $header_label;
                
                continue;
            }
            
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['groups']['#header'][$header_name] = $this->LinkToRemote(
                $header_label,
                'plugg-content',
                $this->getUrl($path, $url_params),
                array(),
                array(),
                $attr
            );
        }
        
        // Fetch groups
        $sorts = array('sticky', $sort); // sticky groups always come first
        $orders = array('DESC', $order);
        $pages = $this->getPluginModel()->Group->paginate(50, $sorts, $orders);
        $page = $pages->getValidPage($request->asInt('p', 1));
        $groups = $page->getElements()->with('LastMember', 'User');
        
        // Fetch group views and stars
        $group_memberships = array();
        if ($this->getUser()->isAuthenticated() && $groups->count()) {
            $group_ids = $groups->getAllIds();
            $members = $this->getPluginModel()->Member->criteria()
                ->userId_is($this->getUser()->id)
                ->groupId_in($group_ids)
                ->fetch();
            foreach ($members as $member) {
                $group_memberships[$member->group_id] = $member;
            }
        }
        
        foreach ($groups as $group) {
            $group_row_class = array();
            
            // Add edit/delete links?
            if ($this->getUser()->isSuperUser()) {
                $links = array(
                    $this->LinkTo($this->_('Edit'), $group->getUrl('settings')),
                    $this->LinkTo($this->_('Delete'), $group->getUrl('settings/delete')),
                );
            } elseif ($this->getUser()->isAuthenticated()) {
                $links = array();
                if (isset($group_memberships[$group->id])) { 
                    if ($group_memberships[$group->id]->isActive() && !$group_memberships[$group->id]->isAdmin()) {
                        $links[] = $this->LinkTo($this->_('Leave group'), $group->getUrl('leave'));
                    }
                } else {
                    $links[] = $this->LinkTo($this->_('Join group'), $group->getUrl('join'));
                }
                if ((isset($group_memberships[$group->id]) && $group_memberships[$group->id]->isAdmin())) {
                    $links[] = $this->LinkTo($this->_('Edit'), $group->getUrl('settings'));
                    $links[] = $this->LinkTo($this->_('Delete'), $group->getUrl('settings/delete'));
                } else {
                    if ($this->getUser()->hasPermission('groups edit any')) {
                        $links[] = $this->LinkTo($this->_('Edit'), $group->getUrl('settings'));
                    }
                    if ($this->getUser()->hasPermission('groups delete any')) {
                        $links[] = $this->LinkTo($this->_('Delete'), $group->getUrl('settings/delete'));
                    }
                }
            } else {
                $links = array();
            }

            $name = sprintf(
                '%s<br /><small>created by %s</small>',
                $this->Groups_GroupLink($group),
                $this->User_IdentityLink($group->User)
            );
            if ($group->LastMember && $group->LastMember->isActive()) {
                $last_member = sprintf(
                    '%s<br /><small>%s</small>',
                    $this->User_IdentityLink($group->LastMember->User),
                    $this->DateTime($group->LastMember->created)
                );
            } else {
                $last_member = '';
            }
            
            $form['groups']['#options'][$group->id] = array(
                'icon' => $this->Groups_GroupThumbnail($group),
                'name' => $name,
                'created' => $this->DateTime($group->created),
                'type' => $group->getTypeStr(),
                'member_count' => $group->member_count,
                'member_lasttime' => $last_member,
                'links' => implode(PHP_EOL, $links),
            );
            
            $form['groups']['#attributes'][$group->id]['@row']['class'] = implode(' ', $group_row_class);
            $form['groups']['#attributes'][$group->id]['@all']['style'] = 'vertical-align:middle;'; // @all for whole row
            
            // Cache for later use
            $this->_groups[$group->id] = $group;
        }
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['groups']['#footer'] = $this->PageNavRemote(
                'plugg-content', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
            $form['groups']['#footer_attributes']['@all'] = array('style' => 'text-align:right;'); 
        }

        return $form;
    }
}
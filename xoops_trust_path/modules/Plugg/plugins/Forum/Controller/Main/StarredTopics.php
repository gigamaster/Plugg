<?php
class Plugg_Forum_Controller_Main_StarredTopics extends Plugg_Form_Controller
{
    private $_actions = array(), $_topics = array(), $_topicViews = array();
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $this->_successUrl = $this->getUrl('/forum/starred');
        $this->_submitButtonLabel = $this->_('Submit');
        $this->_cancelUrl = null;
        
        $form = array(
            'topics' => array(
                '#type' => 'tableselect',
                '#header' => array('star' => ''),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
                '#disabled' => !$this->_submitable,
            )
        );
        
        $headers = array(
            'title' => $this->_('Topic'),
            'attachments' => '',
            'group_id' => $this->_('Group'),
            'comment_count' => $this->_('Replies'),
            'views' => $this->_('Views'),
            'last_posted' => $this->_('Last post'),
            'links' => '',
        );
        
        $sortable_headers = array('title', 'group_id', 'comment_count', 'views', 'last_posted');
        $sort = $request->asStr('sort', 'last_posted', array_keys($sortable_headers));
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        
        // Add headers
        foreach ($headers as $header_name => $header_label) {
            if (!in_array($header_name, $sortable_headers)) {
                $form['topics']['#header'][$header_name] = $header_label;
                
                continue;
            }
            
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['topics']['#header'][$header_name] = $this->LinkToRemote(
                $header_label,
                'plugg-content',
                $this->getUrl('/forum/starred', $url_params),
                array(),
                array(),
                $attr
            );
        }
        
        // Fetch topics
        $model = $this->getPluginModel();
        $gateway = $model->getGateway('Topic');
        $pages = new Sabai_Page_Collection_Custom(
            array($gateway, 'getStarredTopicCount'),
            array($gateway, 'getStarredTopicIds'),
            20,
            array(),
            array($this->getUser()->id, null, 20, 0, $sort, $order)
        );
        $page = $pages->getValidPage($request->asInt('p', 1));
        $topic_ids = iterator_to_array($page->getElements());
        
        // Fetch topics and views
        $topics = $topic_views = array();
        if (count($topic_ids)) {
            $topics = $model->Topic->criteria()->id_in($topic_ids)->fetch(0, 0, array('sticky', $sort), array('DESC', $order))
                ->with('Group')->with('User')->with('LastComment', 'User');
            $views = $this->getPluginModel()->View->criteria()
                ->userId_is($this->getUser()->id)
                ->topicId_in($topic_ids)
                ->fetch();
            foreach ($views as $view) {
                $topic_views[$view->topic_id] = $view;
            }
        }
        
        foreach ($topics as $topic) {
            $topic_path = '/groups/' . $topic->Group->name . '/forum/' . $topic->id;
            $topic_row_class = $topic->getClasses();
            
            // Add edit/delete links?
            if ($this->getUser()->isSuperUser()) {
                $links = array(
                    $this->LinkTo($this->_('Edit'), $this->getUrl($topic_path . '/edit')),
                    $this->LinkTo($this->_('Delete'), $this->getUrl($topic_path . '/delete')),
                );
            } else {
                $links = array();
                if ($topic->isOwnedBy($this->getUser())) {
                    if ($this->getUser()->hasPermission(array('forum topic edit any', 'forum topic edit own'))) {
                        $links[] = $this->LinkTo($this->_('Edit'), $this->getUrl($topic_path . '/edit'));
                    }
                    if ($this->getUser()->hasPermission(array('forum topic delete any', 'forum topic delete own'))) {
                        $links[] = $this->LinkTo($this->_('Delete'), $this->getUrl($topic_path . '/delete'));
                    }
                } else {
                    if ($this->getUser()->hasPermission('forum topic edit any')) {
                        $links[] = $this->LinkTo($this->_('Edit'), $this->getUrl($topic_path . '/edit'));
                    }
                    if ($this->getUser()->hasPermission('forum topic delete any')) {
                        $links[] = $this->LinkTo($this->_('Delete'), $this->getUrl($topic_path . '/delete'));
                    }
                }
            }

            $title = sprintf(
                '%s<br /><small>by %s</small>',
                $this->LinkTo(h($topic->getTitle(100)), $topic_url = $this->getUrl($topic_path), array('title' => $topic->getSummary(150))),
                $this->User_IdentityLink($topic->User)
            );
            if ($topic->LastComment) {
                $last_posted = sprintf(
                    '%s<br /><small>%s <a href="%s" title="%s">&raquo;&raquo;</a></small>',
                    $this->DateTime($topic->LastComment->created),
                    sprintf($this->_('by %s'), $this->User_IdentityLink($topic->LastComment->User)),
                    $this->getUrl($topic_path . '/' . $topic->LastComment->id),
                    $this->_('Go to last post')
                );
            } else {
                $last_posted = sprintf(
                    '%s<br /><small>%s <a href="%s" title="%s">&raquo;&raquo;</a></small>',
                    $this->DateTime($topic->created),
                    sprintf($this->_('by %s'), $this->User_IdentityLink($topic->User)),
                    $topic_url,
                    $this->_('Go to last post')
                );
            }
            
            $form['topics']['#options'][$topic->id] = array(
                'star' => sprintf('<img src="%s" alt="" width="16" height="16 "/>', $this->ImageUrl('Forum', 'star.png')),
                'title' => $title,
                'last_posted' => $last_posted,
                'group_id' => $this->Groups_GroupLink($topic->Group),
                'comment_count' => $topic->comment_count,
                'views' => $topic->views,
                'attachments' => $topic->attachment_count ? sprintf('<img src="%1$s" alt="%2$s" title="%2$s" width="16" height="16" />', $this->ImageUrl('Forum', 'attach.png'), sprintf($this->_n($this->_('1 attachment'), $this->_('%d attachments'), $topic->attachment_count), $topic->attachment_count)) : '',
                'links' => implode(PHP_EOL, $links),
            );
            
            if (isset($topic_views[$topic->id])
                && $topic_views[$topic->id]->last_viewed >= $topic->last_posted
            ) {
                $topic_row_class[] = 'shadow';
                $this->_topicViews[$topic->id] = $topic_views[$topic->id];
            }
            
            $form['topics']['#attributes'][$topic->id]['@row']['class'] = implode(' ', $topic_row_class);
            $form['topics']['#attributes'][$topic->id]['@all']['style'] = 'vertical-align:middle;'; // @all for whole row
            
            // Cache for later use
            $this->_topics[$topic->id] = $topic;
        }
        
        if ($this->_submitable) {
            $this->_actions = array(
                'read' => $this->_('Mark as read'),
                'unread' => $this->_('Mark as unread'),
                //'star' => $this->_('Add star'),
                'unstar' => $this->_('Remove star'),
            );
            if ($this->getUser()->hasPermission('forum topic close any')) {
                $this->_actions['close'] = $this->_('Close');
                $this->_actions['open'] = $this->_('Open');
            }
            if ($this->getUser()->hasPermission('forum topic sticky')) {
                $this->_actions['sticky'] = $this->_('Make sticky');
                $this->_actions['unsticky'] = $this->_('Make unsticky');
            }
            if ($this->getUser()->hasPermission('forum topic delete any')) {
                $this->_actions['delete'] = $this->_('Delete');
            }
            $form[$this->_submitButtonName]['action'] = array(
                '#type' => 'select',
                '#template' => false, // remove title/description parts
                '#field_prefix' => $this->_('Select: '),
                '#options' => $this->_actions,
                '#weight' => -1,
            );
        }
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['topics']['#footer'] = $this->PageNavRemote(
                'plugg-content', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
            $form['topics']['#footer_attributes']['@all'] = array('style' => 'text-align:right;'); 
        }

        return $form;
    }

    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {   
        if ((!$action = @$form->values[$this->_submitButtonName]['action'])
            || !in_array($action, array_keys($this->_actions))
        ) {
            // Invalid action
            return false;
        }
        
        if (empty($form->values['topics'])) return true;

        $model = $this->getPluginModel();
        switch ($action) {
            case 'read':
                foreach ($form->values['topics'] as $topic_id) {
                    if (isset($this->_topicViews[$topic_id])) continue; // already marked as read
                    
                    $view = $model->create('View');
                    $view->topic_id = $topic_id;
                    $view->last_viewed = time();
                    $view->assignUser($this->getUser());
                    $view->markNew();
                }
                break;
                
            case 'unread':
                foreach ($form->values['topics'] as $topic_id) {
                    if (isset($this->_topicViews[$topic_id])) {
                        $this->_topicViews[$topic_id]->markRemoved();
                    }
                }
                break;
                
            case 'unstar':
                $topics_to_unstar = array();
                foreach ($form->values['topics'] as $topic_id) {
                    if (isset($this->_topics[$topic_id])) {
                        $topics_to_unstar[] = $topic_id;
                    }
                }
                if (!empty($topics_to_unstar)) {
                    $stars = $this->getPluginModel()->Star->criteria()
                        ->userId_is($this->getUser()->id)
                        ->topicId_in($topics_to_unstar)
                        ->fetch();
                    foreach ($stars as $star) $star->markRemoved();
                }
                break;
                
            case 'sticky':
                foreach ($form->values['topics'] as $topic_id) {
                    if (isset($this->_topics[$topic_id]) && !$this->_topics[$topic_id]->sticky) {
                        $this->_topics[$topic_id]->sticky = true;
                    }
                }
                break;
                
            case 'unsticky':
                foreach ($form->values['topics'] as $topic_id) {
                    if (isset($this->_topics[$topic_id]) && $this->_topics[$topic_id]->sticky) {
                        $this->_topics[$topic_id]->sticky = false;
                    }
                }
                break;
                
            case 'close':
                foreach ($form->values['topics'] as $topic_id) {
                    if (isset($this->_topics[$topic_id]) && !$this->_topics[$topic_id]->closed) {
                        $this->_topics[$topic_id]->closed = true;
                    }
                }
                break;
                
            case 'open':
                foreach ($form->values['topics'] as $topic_id) {
                    if (isset($this->_topics[$topic_id]) && $this->_topics[$topic_id]->closed) {
                        $this->_topics[$topic_id]->closed = false;
                    }
                }
                break;
                
            case 'delete':
                foreach ($form->values['topics'] as $topic_id) {
                    if (isset($this->_topics[$topic_id])) {
                        $this->_topics[$topic_id]->markRemoved();
                    }
                }
                break;
        }
        
        return $model->commit();
    }
}
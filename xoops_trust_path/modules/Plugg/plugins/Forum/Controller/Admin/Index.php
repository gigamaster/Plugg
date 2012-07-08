<?php
class Plugg_Forum_Controller_Admin_Index extends Plugg_Form_Controller
{
    private $_actions = array(), $_topics = array(), $_topicViews = array(), $_topicStars = array();
    
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $path = '/content/forum';
        $this->_successUrl = $this->getUrl($path);
        $this->_submitButtonLabel = $this->_('Submit');
        $this->_cancelUrl = null;
        
        $form = array(
            'topics' => array(
                '#type' => 'tableselect',
                '#header' => array(),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $sortable_headers = array(
            'title' => $this->_('Topic'),
            'last_posted' => $this->_('Last post'),
            'group_id' => $this->_('Group'),
            'comment_count' => $this->_('Replies'),
            'views' => $this->_('Views'),
            'attachment_count' => $this->_('Attachments'),
            'star_count' => $this->_('Stars'),
        );
        $sort = $request->asStr('sort', 'last_posted', array_keys($sortable_headers));
        $order = $request->asStr('order', 'DESC', array('ASC', 'DESC'));
        
        // Add headers
        foreach ($sortable_headers as $header_name => $header_label) {
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
                $this->getUrl($path, $url_params),
                array(),
                array(),
                $attr
            );
        }
        
        // Fetch topics
        $pages = $this->getPluginModel()->Topic->paginate(20, array('sticky', $sort), array('DESC', $order)); // sticky topics always come first
        $page = $pages->getValidPage($request->asInt('p', 1));
        $topics = $page->getElements()->with('Group')->with('User')->with('LastComment', 'User');
        
        foreach ($topics as $topic) {
            $topic_path = '/groups/' . $topic->Group->name . '/forum/' . $topic->id;
            $topic_row_class = array();

            $title = sprintf(
                '%s<br /><small>by %s</small>',
                $this->LinkTo(h($topic->getTitle(100)), $topic_url = $this->createUrl(array('script' => 'main', 'base' => $topic_path))),
                $this->User_IdentityLink($topic->User)
            );
            if ($topic->LastComment) {
                $last_posted = sprintf(
                    '%s<br /><small>%s <a href="%s" title="%s">&raquo;&raquo;</a></small>',
                    $this->DateTime($topic->LastComment->created),
                    sprintf($this->_('by %s'), $this->User_IdentityLink($topic->LastComment->User)),
                    $this->createUrl(array('script' => 'main', 'base' => $topic_path . '/' . $topic->LastComment->id)),
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
            // Closed?
            if ($topic->closed) {
                $topic_row_class[] = 'forum-topic-closed';
            }
            // Sticky?
            if ($topic->sticky) {
                $topic_row_class[] = 'forum-topic-sticky';
                $topic_row_class[] = 'highlight';
            }
            
            $form['topics']['#options'][$topic->id] = array(
                'attachment_count' => $topic->attachment_count,
                'star_count' => $topic->star_count,
                'title' => $title,
                'last_posted' => $last_posted,
                'group_id' => $this->Groups_GroupLink($topic->Group),
                'comment_count' => $topic->comment_count,
                'views' => $topic->views,
            );
            
            $form['topics']['#attributes'][$topic->id]['@row']['class'] = implode(' ', $topic_row_class);
            $form['topics']['#attributes'][$topic->id]['@all']['style'] = 'vertical-align:middle;'; // @all for whole row
            
            // Cache for later use
            $this->_topics[$topic->id] = $topic;
        }
        
        $this->_actions = array(
            'close' => $this->_('Close'),
            'open' => $this->_('Open'),
            'sticky' => $this->_('Make sticky'),
            'unsticky' => $this->_('Make unsticky'),
            'delete' => $this->_('Delete'),
        );
        $form[$this->_submitButtonName]['action'] = array(
            '#type' => 'select',
            '#template' => false, // remove title/description parts
            '#field_prefix' => $this->_('Select: '),
            '#options' => $this->_actions,
            '#weight' => -1,
        );
        
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
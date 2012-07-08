<?php
class Plugg_Aggregator_Controller_Admin_Feeds_Pending extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $path = '/content/aggregator/feeds/pending';
        $this->_successUrl = $this->getUrl($path);
        $this->_submitButtonLabel = $this->_('Delete');
        $this->_cancelUrl = null;
        
        $form = array(
            'feeds' => array(
                '#type' => 'tableselect',
                '#header' => array(),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $sortable_headers = array(
            'title' => $this->_('Title'),
            'description' => $this->_('Summary'),
            'created' => $this->_('Date added'),
            'user_id' => $this->_('Owner'),
        );
        $sort = $request->asStr('sort', 'created', array_keys($sortable_headers));
        $order = $request->asStr('order', 'ASC', array('ASC', 'DESC'));
        
        // Add headers
        foreach ($sortable_headers as $header_name => $header_label) {
            $attr = array('title' => sprintf($this->_('Sort by %s'), $header_label));    
            if ($sort === $header_name) {
                $url_params = array('sort' => $header_name, 'order' => $order == 'ASC' ? 'DESC' : 'ASC');
                $attr['class'] = 'plugg-' . strtolower($order);
            } else {
                $url_params = array('sort' => $header_name, 'order' => 'ASC');
            }
            $form['feeds']['#header'][$header_name] = $this->LinkToRemote(
                $header_label,
                'plugg-content',
                $this->getUrl($path, $url_params),
                array(),
                array(),
                $attr
            );
        }
        $form['feeds']['#header']['links'] = '';
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Feed->criteria()
            ->status_is(Plugg_Aggregator_Plugin::FEED_STATUS_PENDING)
            ->paginate(20, $sort, $order);
        $page = $pages->getValidPage($request->asInt('p', 1));
        $feeds = $page->getElements();
        foreach ($feeds->with('User') as $feed) {
            $feed_path = '/content/aggregator/feeds/' . $feed->id;
            $links = array(
                $this->LinkToRemote($this->_('Details'), 'plugg-content', $this->getUrl($feed_path)),
                $this->LinkToRemote($this->_('Edit'), 'plugg-content', $this->getUrl($feed_path . '/edit')),
            );
            $title = sprintf(
                '<small><a href="%1$s" title="%1$s">%2$s</a></small><br />%4$s%3$s',
                h($feed->site_url),
                h(mb_strimlength($feed->site_url, 0, 50)),
                $this->LinkTo(h(mb_strimlength($feed->title, 0, 100)), array('script' => 'main', 'base' => '/aggregator', 'path' => $feed->id)),
                ($feed->favicon_url && !$feed->favicon_hide) ? sprintf('<img src="%s" alt="" width="16" height="16" style="vertical-align:middle; margin-right:4px; padding:2px 0;" />', h($feed->favicon_url)) : ''
            );
            $form['feeds']['#options'][$feed->id] = array(
                'title' => $title,
                'created' => $this->DateTime($feed->created),
                'description' => $feed->description,
                'user_id' => $feed->user_id ? $this->User_IdentityLink($feed->User) : '',
            );
        }
        $form[$this->_submitButtonName]['approve'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Approve'),
            '#submit' => array(array($this, 'approveFeeds')),
            '#weight' => -3,
        );
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['feeds']['#footer'] = $this->PageNavRemote(
                'plugg-content', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['feeds'])) return true;
        
        $model = $this->getPluginModel();
        foreach ($model->Feed->criteria()->id_in($form->values['feeds'])->fetch() as $feed) {
            $feed->markRemoved();
        }
        
        return $model->commit();
    }
    
    public function approveFeeds(Plugg_Form_Form $form)
    {
        if (empty($form->values['feeds'])) return true;
        
        $model = $this->getPluginModel();
        $feeds = $model->Feed->criteria()
            ->status_is(Plugg_Aggregator_Plugin::FEED_STATUS_PENDING)
            ->id_in($form->values['feeds'])
            ->fetch();
        foreach ($feeds as $feed) {
            $feed->setApproved();
        }

        if (false === $ret = $model->commit()) {
            return false;
        }

        foreach ($feeds as $feed) {
            $this->getPlugin()->sendFeedApprovedEmail($feed);
        }

        return $ret;
    }
}
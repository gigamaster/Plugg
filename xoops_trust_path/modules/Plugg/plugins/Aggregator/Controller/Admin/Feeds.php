<?php
class Plugg_Aggregator_Controller_Admin_Feeds extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $path = '/content/aggregator/feeds';
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
            'user_id' => $this->_('Owner'),
            'created' => $this->_('Date added'),
            'last_publish' => $this->_('Last published'),
            'last_fetch' => $this->_('Last fetched'),
            'item_count' => $this->_('Articles'),
        );
        $sort = $request->asStr('sort', 'last_publish', array_keys($sortable_headers));
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
            ->status_is(Plugg_Aggregator_Plugin::FEED_STATUS_APPROVED)
            ->paginate(20, $sort, $order);
        $page = $pages->getValidPage($request->asInt('p', 1));
        $feeds = $page->getElements();
        foreach ($feeds as $feed) {
            $feed_path = $path . '/' . $feed->id;
            $links = array(
                $this->LinkToRemote($this->_('Details'), 'plugg-content', $this->getUrl($feed_path)),
                $this->LinkToRemote($this->_('Edit'), 'plugg-content', $this->getUrl($feed_path . '/edit')),
            );
            $title = sprintf(
                '<small><a href="%1$s" title="%5$s">%2$s</a></small><br />%4$s%3$s',
                h($feed->site_url),
                h(mb_strimlength($feed->site_url, 0, 50)),
                $this->LinkTo(h(mb_strimlength($feed->title, 0, 100)), array('script' => 'main', 'base' => '/aggregator', 'path' => $feed->id)),
                ($feed->favicon_url && !$feed->favicon_hide) ? sprintf('<img src="%s" alt="" width="16" height="16" style="vertical-align:middle; margin-right:4px; padding:2px 0;" />', h($feed->favicon_url)) : '',
                h($feed->title)
            );
            $form['feeds']['#options'][$feed->id] = array(
                'title' => $title,
                'created' => $this->DateTime($feed->created),
                'last_publish' => $feed->last_publish ? $this->DateTime($feed->last_publish) : '',
                'last_fetch' => $feed->last_fetch ? $this->DateTime($feed->last_fetch) : '',
                'item_count' => $feed->item_count,
                'links' => implode(PHP_EOL, $links),
                'user_id' => $feed->user_id ? $this->User_IdentityLink($feed->User) : '',
            );
        }
        $form[$this->_submitButtonName]['fetch'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Fetch articles'),
            '#submit' => array(array($this, 'updateFeeds')),
            '#weight' => -2,
        );
        $form[$this->_submitButtonName]['empty'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Empty'),
            '#submit' => array(array($this, 'emptyFeeds')),
            '#weight' => -1,
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
    
    public function emptyFeeds(Plugg_Form_Form $form)
    {
        if (empty($form->values['feeds'])) return true;
        
        $model = $this->getPluginModel();
        // Delete all items associated with the selected feeds
        $criteria = $model->createCriteria('Item')->feedId_in($form->values['feeds']);
        if (false === $model->getGateway('Item')->deleteByCriteria($criteria)) {
            return false;
        }

        // Now, reset all statistic data for the feeds
        $feeds = $model->Feed->criteria()->id_in($form->values['feeds'])->fetch();
        foreach ($feeds as $feed) {
            $feed->set(array(
                'last_fetch' => 0,
                'last_ping' => 0,
                'last_publish' => 0,
                'item_last' => 0,
                'item_count' => 0,
                'item_lasttime' => $feed->created
            ));
        }
        
        return $model->commit();
    }
    
    public function updateFeeds(Plugg_Form_Form $form)
    {
        if (empty($form->values['feeds'])) return true;
        
        $count = false;
        $model = $this->getPluginModel();
        $feeds = $model->Feed->criteria()->status_is(Plugg_Aggregator_Plugin::FEED_STATUS_APPROVED)
            ->id_in($form->values['feeds'])->fetch();
        foreach ($feeds as $feed) {
            if (false !== $this->getPlugin()->loadFeedItems($feed)) {
                $count = $count + 1;
            }
        }

        return $count;
    }
}
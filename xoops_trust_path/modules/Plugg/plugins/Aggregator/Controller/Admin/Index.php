<?php
class Plugg_Aggregator_Controller_Admin_Index extends Plugg_Form_Controller
{
    protected function _doGetFormSettings(Sabai_Request $request, Sabai_Application_Response $response, array &$formStorage)
    {
        $path = '/content/aggregator';
        $this->_successUrl = $this->getUrl($path);
        $this->_submitButtonLabel = $this->_('Delete');
        $this->_cancelUrl = null;
        
        $form = array(
            'items' => array(
                '#type' => 'tableselect',
                '#header' => array(),
                '#multiple' => true,
                '#js_select' => true,
                '#options' => array(),
            )
        );
        
        $sortable_headers = array(
            'title' => $this->_('Title'),
            'published' => $this->_('Published'),
            'summary' => $this->_('Summary'),
            'feed_id' => $this->_('Feed'),
        );
        $sort = $request->asStr('sort', 'published', array_keys($sortable_headers));
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
            $form['items']['#header'][$header_name] = $this->LinkToRemote(
                $header_label,
                'plugg-content',
                $this->getUrl($path, $url_params),
                array(),
                array(),
                $attr
            );
        }
        $form['items']['#header']['links'] = '';
        
        // Add rows (options)
        $pages = $this->getPluginModel()->Item->paginate(20, $sort, $order);
        $page = $pages->getValidPage($request->asInt('p', 1));
        $items = $page->getElements();
        foreach ($items->with('Feed') as $item) {
            $item_path = $path . '/feeds/' . $item->Feed->id . '/' . $item->id;
            $links = array(
                $this->LinkToRemote($this->_('Edit'), 'plugg-content', $this->getUrl($item_path . '/edit')),
            );
            $title = sprintf(
                '<small><a href="%1$s" title="%4$s">%2$s</a></small><br />%3$s',
                h($item->url),
                h(mb_strimlength($item->url, 0, 50)),
                $this->LinkTo(h(mb_strimlength($item->title, 0, 100)), array('script' => 'main', 'base' => '/aggregator', 'path' => $item->Feed->id . '/' . $item->id)),
                h($item->title)
            );
            $published = $this->DateTime($item->published) . '<br />';
            if ($item->author) {
                $published .= sprintf('<small>%s</small>', sprintf($this->_(' by %s'), h($item->author)));
            }
            if ($categories = $item->getCategories()) {
                $published .= sprintf('<small>%s</small>', sprintf($this->_(' in %s'), implode(', ', array_map('h', $categories))));
            }
            $form['items']['#options'][$item->id] = array(
                'title' => $title,
                'published' => $published,
                'summary' => h($item->getSummary(150)),
                'feed_id' => $this->LinkToRemote(h($item->Feed->title), 'plugg-content', $this->getUrl($path . '/feeds/' . $item->Feed->id)),
                'links' => implode(PHP_EOL, $links),
            );
            if ($item->hidden) $form['items']['#attributes'][$item->id]['@row']['class'] = 'shadow'; // @all for whole row
        }
        
        $form[$this->_submitButtonName]['hide'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Hide'),
            '#submit' => array(array($this, 'hideItems')),
            '#weight' => -2,
        );
        $form[$this->_submitButtonName]['unhide'] = array(
            '#type' => 'submit',
            '#value' => $this->_('Unhide'),
            '#submit' => array(array(array($this, 'hideItems'), array(false))),
            '#weight' => -1,
        );
        
        // Add page navigation to the footer if more than a page
        if ($pages->count() > 1) {
            $form['items']['#footer'] = $this->PageNavRemote(
                'plugg-content', $pages, $page->getPageNumber(), $this->getUrl($path, array('sort' => $sort, 'order' => $order))
            );
        }
        
        return $form;
    }
    
    public function submitForm(Plugg_Form_Form $form, Sabai_Request $request, Sabai_Application_Response $response)
    {
        if (empty($form->values['items'])) return true;
        
        $model = $this->getPluginModel();
        foreach ($model->Item->criteria()->id_in($form->values['items'])->fetch() as $item) {
            $item->markRemoved();
        }
        
        return $model->commit();
    }
    
    public function hideItems(Plugg_Form_Form $form, $hidden = true)
    {
        if (empty($form->values['items'])) return true;
        
        $model = $this->getPluginModel();
        $items = $model->Item->criteria()->hidden_is(!$hidden)->id_in($form->values['items'])->fetch();
        $feeds = array();
        foreach ($items->with('Feed') as $item) {
            $item->hidden = $hidden;
            $feeds[$item->Feed->id] = $item->Feed;
        }
        
        // Commit items first to update item data
        if (!$ret = $model->commit()) return false;

        if (count($feeds)) {
            foreach ($feeds as $feed) {
                $feed->updateLastPublished(false);
            }
            $model->commit();
        }

        return $ret;
    }
}
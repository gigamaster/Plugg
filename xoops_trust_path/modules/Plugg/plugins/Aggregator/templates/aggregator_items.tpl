<?php
$list_items = array();
foreach ($items as $item) {
    $feed = $item->Feed;
    $links = $meta_html = array();
    if ($item->author && $feed->author_pref == Plugg_Aggregator_Plugin::FEED_AUTHOR_PREF_ENTRY_AUTHOR) {
        $meta_html[] = $item->getAuthorHTMLLink();
    } elseif ($feed->user_id) {
        $meta_html[] = $this->User_IdentityLink($feed->User);
    }
    $feed_link= $this->LinkToRemote($feed->title, 'plugg-content', $this->Url('/aggregator/' . $feed->id));
    if ($categories = $item->getCategories()) {
        $meta_html[] = sprintf($this->_('Feed: %s in %s'), $feed_link, implode(', ', array_map('h', $categories)));
    } else {
        $meta_html[] = sprintf($this->_('Feed: %s'), $feed_link);
    }

    if ($feed->isOwnedBy($this->User())) {
        $edit_permission = array('aggregator item edit any', 'aggregator item edit own');
        $delete_permission = array('aggregator item delete any', 'aggregator item delete own');
    } else {
        $edit_permission = array('aggregator item edit any');
        $delete_permission = array('aggregator item delete any');
    }
    if ($this->User()->hasPermission($edit_permission)) {
        $links[] = array(
            'url' => $this->Url('/aggregator/' . $feed->id . '/' . $item->id . '/edit'),
            'attributes' => array('title' => $this->_('Edit this feed article')),
            'text' => $this->_('Edit')
        );
    }
    if ($this->User()->hasPermission($delete_permission)) {
        $links[] = array(
            'url' => $this->Url('/aggregator/' . $feed->id . '/' . $item->id . '/delete'),
            'attributes' => array('title' => $this->_('Delete this feed article')),
            'text' => $this->_('Delete')
        );
    }
    $links[] = array(
        'url' => $item->url,
        'attributes' => array('title' => $item->title),
        'text' => $this->_('Original article')
    );
    $list_items[] = array(
        'title' => $item->title,
        'url' => $this->Url('/aggregator/' . $feed->id . '/' . $item->id),
        'timestamp' => $item->published,
        'meta_html' => $meta_html,
        'body_html' => $item->body,
        'links' => $links
    );
}
?>
<?php $this->_includeTemplate('plugg_list', array('list' => array('items' => $list_items)));?>
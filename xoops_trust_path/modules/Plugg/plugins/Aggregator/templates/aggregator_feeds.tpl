<?php
$list_items = array();
foreach ($feeds as $feed) {
    $links = array();
    $meta_html = !$feed->User->isAnonymous() ? array($this->User_IdentityLink($feed->User)) : array();
    if ($feed->last_publish) $meta_html[] = sprintf($this->_('Last published: %s'), $this->DateTime($feed->last_publish));

    if ($feed->isOwnedBy($this->User())) {
        $edit_permission = array('aggregator feed edit any', 'aggregator feed edit own');
        $delete_permission = array('aggregator feed delete any', 'aggregator feed delete own');
    } else {
        $edit_permission = array('aggregator feed edit any');
        $delete_permission = array('aggregator feed delete any');
    }
    if ($this->User()->hasPermission($edit_permission)) {
        $links[] = array(
            'url' => isset($identity) ? $this->Url('/user/' . $identity->id . '/aggregator/' . $feed->id . '/edit') : $this->Url('/aggregator/feeds/' . $feed->id . '/edit'),
            'text' => $this->_('Edit')
        );
    }
    if ($this->User()->hasPermission($delete_permission)) {
        $links[] = array(
            'url' => isset($identity) ? $this->Url('/user/' . $identity->id . '/aggregator/' . $feed->id . '/remove') : $this->Url('/aggregator/feeds/' . $feed->id . '/remove'),
            'text' => $this->_('Remove')
        );
    }
    $links[] = array(
        'url' => $this->Url('/aggregator/' . $feed->id),
        'attributes' => array('title' => $this->_('View articles of this feed')),
        'text' => sprintf($this->_n('1 article', '%d articles', $feed->item_count), $feed->item_count)
    );
    $links[] = array(
        'url' => $feed->site_url,
        'text' => $this->_('Visit website'),
        'attributes' => array('title' => $feed->title),
    );
    $list_items[] = array(
        'image' => $feed->getScreenshotUrl(),
        'title_html' => $feed->favicon_url && !$feed->favicon_hide ? sprintf('<img src="%s" width="16" height="16" alt="" /> %s', h($feed->favicon_url), h($feed->title)) : h($feed->title),
        'timestamp' => $feed->created,
        'meta_html' => $meta_html,
        'body' => $feed->description,
        'links' => $links
    );
}
?>
<?php $this->_includeTemplate('plugg_list', array('list' => array('items' => $list_items)));?>
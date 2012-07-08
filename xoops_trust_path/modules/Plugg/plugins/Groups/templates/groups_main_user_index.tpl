<?php
$pagination = $this->PageNavRemote(
    'plugg-content',
    $member_pages,
    $member_page->getPageNumber(),
    array()
);
$list_items = array();
foreach ($members as $member) {
    $group = $member->Group;
    $links = array();
    if ($identity->id == $this->User()->id) {
        $links[] = array(
            'url' => $group->getUrl('leave'),
            'text' => $this->_('Leave group')
        );
    }
    $list_items[] = array(
        'thumbnail' => $group->getAvatarThumbnailUrl(),
        'thumbnail_link' => $group->getUrl(),
        'thumbnail_title' => $group->display_name,
        'url' => $group->getUrl(),
        'title' => $group->display_name,
        'body' => $group->getSummary(),
        'timestamp' => $member->created,
        'timestamp_label' => $this->_('Member since: '),
        'links' => $links,
        'meta_html' => array(sprintf($this->_n('1 member', '%d members', $group->getCount('ActiveMember')), $group->getCount('ActiveMember')))
    );
}
?>
<?php $this->_includeTemplate('plugg_list', array('list' => array('items' => $list_items)));?>
<div class="groups-pagination"><?php echo $pagination;?></div>
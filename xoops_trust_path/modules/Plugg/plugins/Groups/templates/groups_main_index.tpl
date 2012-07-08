<?php
$pagination = $this->PageNavRemote(
    'plugg-content',
    $pages,
    $page->getPageNumber(),
    array()
);

$list_items = array();
foreach ($groups->with('MemberCount') as $group) {
    $links = $meta_html = array();
    $meta_html = array(
        sprintf($this->_n('%d member', '%d members', $group->getCount('ActiveMember')), $group->getCount('ActiveMember')),
        $group->getTypeStr(),
    );

    if (isset($groups_i_belong[$group->id])) {
        if ($groups_i_belong[$group->id]->isActive() && !$groups_i_belong[$group->id]->isAdmin()) {
            $links[] = array(
                'url' => $group->getUrl('leave'),
                'text' => $this->_('Leave group')
            );
        }
    } else {
        if ($group->type == Plugg_Groups_Plugin::GROUP_TYPE_NONE) {
            $links[] = array(
                'url' => $group->getUrl('join'),
                'text' => $this->_('Join group')
            );
        } elseif ($group->type == Plugg_Groups_Plugin::GROUP_TYPE_APPROVAL_REQUIRED) {
            $links[] = array(
                'url' => $group->getUrl('join'),
                'text' => $this->_('Request membership')
            );
        }
    }

    $list_items[] = array(
        'image' => $group->getAvatarUrl(),
        'title' => $group->display_name,
        'url' => $group->getUrl(),
        'timestamp' => $group->created,
        'user' => $group->User,
        'meta_html' => $meta_html,
        'body' => $group->getSummary(),
        'links' => $links
    );
}
?>
<?php $this->_includeTemplate('plugg_list', array('list' => array('items' => $list_items)));?>

<div class="groups-pagination"><?php echo $pagination;?></div>
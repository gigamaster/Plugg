<?php
$pagination = $this->PageNavRemote(
    'plugg-content',
    $member_pages,
    $member_page->getPageNumber(),
    array('path' => 'members')
);
$images = array();
foreach ($members as $member) {
    if ($member->User->isAnonymous()) continue;
    $images[] = array(
        'url' => $member->User->image_thumbnail,
        'link' => $this->User_IdentityUrl($member->User),
        'title' => $member->User->display_name,
        'caption_html' => $this->User_IdentityLink($member->User)
    );
}
?>
<?php $this->_includeTemplate('plugg_gallery', array('gallery' => array('images' => $images)));?>
<div class="groups-pagination"><?php echo $pagination;?></div>
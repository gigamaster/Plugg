<?php
$pagination = $this->PageNavRemote(
    'plugg-content',
    $friend_pages,
    $friend_page->getPageNumber(),
    array()
);
$images = array();
foreach ($friends->with('WithUser') as $friend) {
    $friend_identity = $friend->WithUser;
    if ($friend_identity->isAnonymous()) continue;
    $images[] = array(
        'url' => $friend_identity->image_thumbnail,
        'link' => $this->User_IdentityUrl($friend_identity),
        'title' => $friend_identity->display_name,
        'rel' => $friend->relationships,
        'caption_html' => $this->User_IdentityLink($friend_identity)
    );
}
$gallery = array(
    'images' => $images
);
?>
<?php $this->_includeTemplate('plugg_gallery', array('gallery' => $gallery));?>
<div class="friends-pagination"><?php echo $pagination;?></div>
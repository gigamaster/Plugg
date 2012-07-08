<?php
$pagination = $this->PageNavRemote(
    'plugg-content',
    $pages,
    $page->getPageNumber(),
    array()
);
$images = array();
foreach ($footprints as $footprint) {
    if ($footprint->User->isAnonymous()) continue;
    $images[] = array(
        'url' => $footprint->User->image_thumbnail,
        'link' => $this->User_IdentityUrl($footprint->User),
        'title' => $footprint->User->display_name,
        'caption_html' => $this->User_IdentityLink($footprint->User) . '<br />' . $this->DateTime($footprint->timestamp),
    );
}
$gallery = array(
    'images' => $images
);
$this->_includeTemplate('plugg_gallery', array('gallery' => $gallery));
?>
<div class="footprints-pagination"><?php echo $pagination;?></div>
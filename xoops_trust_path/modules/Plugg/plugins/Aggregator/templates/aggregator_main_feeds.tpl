<div class="aggregator-feeds">
<?php if ($feeds->count() > 0):
$nav_pages = $this->PageNavRemote(
    'plugg-content',
    $pages,
    $page->getPageNumber(),
    array('path' => 'feeds')
);
?>
<?php   $this->_includeTemplate('aggregator_feeds', array('feeds' => $feeds));?>
  <div class="aggregator-pagination"><?php echo $nav_pages;?></div>
<?php endif;?>
</div>
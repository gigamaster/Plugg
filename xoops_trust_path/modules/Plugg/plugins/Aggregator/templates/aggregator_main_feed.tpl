<div class="aggregator-items">
<?php if (!empty($items)):
$nav_pages = $this->PageNavRemote(
    'plugg-content',
    $pages,
    $page->getPageNumber(),
    array('path' => $feed->id)
);
?>
<?php   $this->_includeTemplate('aggregator_items', array('items' => $items));?>
  <div class="aggregator-pagination"><?php echo $nav_pages;?></div>
<?php endif;?>
</div>
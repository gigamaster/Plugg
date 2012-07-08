<ul class="plugg-tabs">
  <li class="plugg-tab-selected">
    <?php echo $this->LinkToRemote(sprintf($this->_('Comments (%d)'), $topic->comment_count), 'plugg-forum-comments', $group->getUrl('forum/' . $topic->id), $group->getUrl('forum/' . $topic->id . '/comments'), array(), array('class' => 'plugg-tab-label'));?>
  </li>
</ul>
<?php $this->_includeTemplate('forum_comments', array('topic' => $topic, 'group' => $group, 'comments' => $comments));?>
<div class="forum-pagination">
<?php //if ($ajax_target = $this->isAjax()):?>
<?php   echo $this->PageNavRemote('plugg-forum-comments', $comment_pages, $comment_page->getPageNumber(), $group->getUrl('forum/' . $topic->id), $group->getUrl('forum/' . $topic->id . '/comments'));?>
<?php //else:?>
<?php //  echo $this->PageNav($comment_pages, $comment_page->getPageNumber(), $group->getUrl('forum/' . $topic->id . '/comments'));?>
<?php //endif;?>
</div>
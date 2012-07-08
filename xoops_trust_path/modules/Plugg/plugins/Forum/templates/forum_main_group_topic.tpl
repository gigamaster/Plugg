<div class="forum-topic">
<?php
$links = array();
if (!$topic->closed) {
    $links[] = array(
        'url' => $this->Url('/groups/' . $group->name . '/forum/' . $topic->id . '/add_comment'),
        'ajax' => true,
        'ajaxOptions' => array('content' => "target.find('form textarea').focusRange(0, 0);"),
        'ajaxTarget' => 'plugg-forum-topic-form' . $topic->id,
        'attributes' => array('title' => $this->_('Reply to this message')),
        'text' => $this->_('Reply'),
    );
}

if ($topic->isOwnedBy($this->User())) {
    $edit_permission = array('forum topic edit any', 'forum topic edit own');
    $delete_permission = array('forum topic delete any', 'forum topic delete own');
} else {
    $edit_permission = array('forum topic edit any');
    $delete_permission = array('forum topic delete any');
}
if ($this->User()->hasPermission($edit_permission)) {
    $links[] = array(
        'url' => array('path' => $topic->id . '/edit'),
        'attributes' => array('title' => $this->_('Edit this message')),
        'text' => $this->_('Edit'),
        'ajax' => true,
        'ajaxOptions' => array('content' => "target.focusFirstInput();"),
        'ajaxTarget' => 'plugg-forum-topic' . $topic->id . ' .plugg-item-content',
    );
}
if ($this->User()->hasPermission($delete_permission)) {
    $links[] = array(
        'url' => array('path' => $topic->id . '/delete'),
        'attributes' => array('title' => $this->_('Delete this message')),
        'text' => $this->_('Delete'),
        'ajax' => true,
        'ajaxTarget' => 'plugg-forum-topic' . $topic->id . ' .plugg-item-content',
    );
}

$this->_includeTemplate('plugg_item', array('item' => array(
    'id' => 'plugg-forum-topic' . $topic->id,
    'title' => $topic->getTitle(),
    'thumbnail' => $topic->User->image_thumbnail,
    'thumbnail_link' => $this->User_IdentityUrl($topic->User),
    'thumbnail_title' => $topic->User->display_name,
    'class' => implode(' ', $topic->getClasses()),
    'timestamp' => $topic->created,
    'user' => $topic->User,
    'meta_html' => array(
        sprintf(
            '<a href="%1$s" title="%2$s">#%3$d</a>',
            $group->getUrl('forum/' . $topic->id),
            $this->_('Permalink'),
            $topic->id
        ),
    ),
    'body_html' => $topic->body_html,
    'links' => $links,
    'attachments' => ($file_ids = $topic->getAttachmentFileIds()) ? $this->Uploads_FileList($file_ids) : array(),
    'extra_html' => sprintf('<div class="forum-topic-form" id="plugg-forum-topic-form%d"></div>', $topic->id)
)));
?>
  <div class="forum-comments" id="plugg-forum-comments">
    <ul class="plugg-tabs">
      <li class="plugg-tab-selected">
        <?php echo $this->LinkToRemote(sprintf($this->_('Comments (%d)'), $topic->comment_count), 'plugg-forum-comments', $this->Url('/groups/' . $group->name . '/forum/' . $topic->id), $this->Url('/groups/' . $group->name . '/forum/' . $topic->id . '/comments'), array(), array('class' => 'plugg-tab-label'));?>
      </li>
    </ul>
<?php $this->_includeTemplate('forum_comments', array('topic' => $topic, 'group' => $group, 'comments' => $comments));?>
    <div class="forum-pagination"><?php echo $this->PageNavRemote('plugg-forum-comments', $comment_pages, $comment_page->getPageNumber(), $this->Url('/groups/' . $group->name . '/forum/' . $topic->id), $this->Url('/groups/' . $group->name . '/forum/' . $topic->id . '/comments'));?></div>
  </div>
</div>
<?php
$list_items = array();
$comment_ids = $comments->getAllIds();
foreach ($comments as $comment) {
    $links = array();
    if (!$topic->closed) {
        $links[] = array(
            'url' => $group->getUrl('forum/' . $comment->topic_id . '/' . $comment->id . '/reply'),
            'attributes' => array('title' => $this->_('Post a new comment')),
            'text' => $this->_('Reply'),
            'ajax' => true,
            'ajaxOptions' => array('content' => "target.find('form textarea:first').focusRange(0, 0);"),
            'ajaxTarget' => 'plugg-forum-comment-form' . $comment->id,
        );
    }
    if ($comment->isOwnedBy($this->User())) {
        $edit_permission = array('forum comment edit any', 'forum comment edit own');
        $delete_permission = array('forum comment delete any', 'forum comment delete own');
    } else {
        $edit_permission = array('forum comment edit any');
        $delete_permission = array('forum comment delete any');
    }
    if ($this->User()->hasPermission($edit_permission)) {
        $links[] = array(
            'url' => $group->getUrl('forum/' . $comment->topic_id . '/' . $comment->id . '/edit'),
            'attributes' => array('title' => $this->_('Edit this comment')),
            'text' => $this->_('Edit'),
            'ajax' => true,
            'ajaxOptions' => array('content' => "target.find('form textarea:first').focusRange(0, 0);"),
            'ajaxTarget' => 'plugg-forum-comment' . $comment->id . ' .plugg-item-content'
        );
    }
    if ($this->User()->hasPermission($delete_permission)) {
        $links[] = array(
            'url' => $group->getUrl('forum/' . $comment->topic_id . '/' . $comment->id . '/delete'),
            'attributes' => array('title' => $this->_('Delete this comment')),
            'text' => $this->_('Delete'),
            'ajax' => true,
            'ajaxTarget' => 'plugg-forum-comment' . $comment->id . ' .plugg-item-content'
        );
    }

    $meta_html = array(
        sprintf(
            '<a href="%1$s" title="%2$s">#%3$d-%4$d</a>',
            $group->getUrl('forum/' . $comment->topic_id . '/' . $comment->id),
            $this->_('Permalink'),
            $comment->topic_id,
            $comment->id
        )
    );
    if ($comment->parent) {
        if (!in_array($comment->parent, $comment_ids)) {
            $meta_html[] = sprintf(
                $this->_('Posted in reply to <a href="%1$s">#%2$d-%3$d</a>'),
                $group->getUrl('forum/' . $comment->topic_id . '/' . $comment->parent),
                $comment->topic_id,
                $comment->parent
            );
        } else {
            $meta_html[] = sprintf(
                $this->_('Posted in reply to <a href="#plugg-forum-comment%2$d">#%1$d-%2$d</a>'),
                $comment->topic_id,
                $comment->parent
            );
        }
    }
    $list_items[] = array(
        'id' => 'plugg-forum-comment' . $comment->id,
        'title' => $comment->title,
        'thumbnail' => $comment->User->image_thumbnail,
        'thumbnail_link' => $this->User_IdentityUrl($comment->User),
        'thumbnail_title' => $comment->User->display_name,
        'timestamp' => $comment->created,
        'user' => $comment->User,
        'meta_html' => $meta_html,
        'body_html' => $comment->body_html,
        'links' => $links,
        'attachments' => ($file_ids = $comment->getAttachmentFileIds()) ? $this->Uploads_FileList($file_ids) : array(),
        'extra_html' => sprintf('<div class="forum-comment-form" id="plugg-forum-comment-form%d"></div>', $comment->id)
    );
}
$this->_includeTemplate('plugg_list', array('list' => array('items' => $list_items)));
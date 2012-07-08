<?php
class Plugg_Forum_Controller_Main_Group_Topic_Comment  extends Sabai_Application_Controller
{
    protected function _doExecute(Sabai_Request $request, Sabai_Application_Response $response)
    {
        $comment_count = $this->getPluginModel()->Comment
            ->criteria()
            ->topicId_is($this->topic->id)
            ->created_isSmallerThan($this->comment->created)
            ->count();

        header('Location: ' . $this->getUrl(
            '/groups/' . $this->group->name . '/forum/' . $this->topic->id,
            array(
                'p' => intval(ceil(($comment_count + 1) / 20)),
                'order' => $order
            ),
            'plugg-forum-comment' . $this->comment->id,
            '&'
        ));

        exit;
    }
}
<?php
class Plugg_Aggregator_Model_Feed extends Plugg_Aggregator_Model_Base_Feed
{
    var $_simplePie;

    public function isApproved()
    {
        return $this->status == Plugg_Aggregator_Plugin::FEED_STATUS_APPROVED;
    }

    public function setApproved()
    {
        $this->status = Plugg_Aggregator_Plugin::FEED_STATUS_APPROVED;
    }

    public function getHTMLLink()
    {
        return sprintf('<a href="%s">%s</a>', h($this->site_url), h($this->title));
    }

    function getScreenshot($width = 100, $height = 100)
    {
        return sprintf('<img src="http://mozshot.nemui.org/shot?img_x=%1$d:img_y=%2$d;effect=true;uri=%3$s" width="%1$d" height="%2$d" alt="" />', $width, $height, urlencode($this->site_url));
    }

    function getScreenshotUrl($width = 100, $height = 100)
    {
        return sprintf('http://mozshot.nemui.org/shot?img_x=%1$d:img_y=%2$d;effect=true;uri=%3$s', $width, $height, urlencode($this->site_url));
    }

    public function updateLastPublished($commit = true)
    {
        if (!$this->item_count) return;

        $items = $this->_model->Item
            ->criteria()
            ->hidden_is(0)
            ->fetchByFeed($this->id, 1, 0, 'item_published', 'DESC');
        if ($items->count() != 1) return;

        $item = $items->getFirst();
        if ($this->last_publish == $item->published) {
            return;
        }

        $this->last_publish = $item->published;

        if (!$commit) return;

        return $this->commit();
    }
}

class Plugg_Aggregator_Model_FeedRepository extends Plugg_Aggregator_Model_Base_FeedRepository
{
}
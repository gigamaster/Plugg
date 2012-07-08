<?php
interface Plugg_XOOPSCube_SearchableModule
{
    public function searchGetTitle();
    public function searchGetContentUrl($contentId);
    public function searchFetchContents($limit, $offset);
    public function searchCountContents();
    public function searchFetchContentsSince($timestamp, $limit, $offset);
    public function searchCountContentsSince($timestamp);
    public function searchFetchContentsByIds($contentIds);
}
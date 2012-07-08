<?php
class Plugg_Helper_PageNav extends Sabai_Application_Helper
{
    public function help(Sabai_Application $application, Sabai_Page_Collection $pages, $currentPage, $linkUrl, $showRange = true, $pageVar = null, $offset = 3)
    {
        if ($page = $pages->getPage($currentPage)) {
            if ($showRange) {
                $last = $page->getOffset() + $page->getLimit();
                $first = $last > 0 ? $page->getOffset() + 1 : 0;
                $current_html = sprintf('<li class="plugg-pagination-current">%d (%d-%d/%d)</li>', $currentPage, $first, $last, $pages->getElementCount());
            } else {
                $current_html = sprintf('<li class="plugg-pagination-current">%d</li>', $currentPage);
            }
        } else {
            $current_html = '';
        }
        $nav_html = array();

        if (1 < $page_count = $pages->count()) {

            if (empty($pageVar)) $pageVar = 'p';

            $link_url = is_array($linkUrl) ? array_merge(array('params' => array()), $linkUrl) : $linkUrl;
            if ($currentPage == 1) {
                $nav_html[] = '<li class="plugg-pagination-first">&laquo;</li>';
                $nav_html[] = '<li class="plugg-pagination-previous">&lsaquo;</li>';
            } else {
                $nav_html[] = sprintf('<li class="plugg-pagination-first">%s</li>', $this->_getPageLink($application, '&laquo;', 1, $link_url, $pageVar));
                $nav_html[] = sprintf('<li class="plugg-pagination-previous">%s</li>', $this->_getPageLink($application, '&lsaquo;', $currentPage - 1, $link_url, $pageVar));
            }
            $max = $currentPage + $offset;
            for ($i = max(1, $currentPage - $offset); $i <= $max; $i++) {
                if (!$pages->hasPage($i)) continue;
                $nav_html[] = ($i == $currentPage) ? $current_html : sprintf('<li>%s</li>', $this->_getPageLink($application, $i, $i, $link_url, $pageVar));
            }
            if ($currentPage == $page_count) {
                $nav_html[] = '<li class="plugg-pagination-next">&rsaquo;</li>';
                $nav_html[] = '<li class="plugg-pagination-last">&raquo;</li>';
            } else {
                $nav_html[] = sprintf('<li class="plugg-pagination-next">%s</li>', $this->_getPageLink($application, '&rsaquo;', $currentPage + 1, $link_url, $pageVar));
                $nav_html[] = sprintf('<li class="plugg-pagination-last">%s</li>', $this->_getPageLink($application, '&raquo;', $page_count, $link_url, $pageVar));
            }
        }

        return sprintf('<ul class="plugg-pagination">%s</ul>', implode(PHP_EOL, $nav_html));
    }

    private function _getPageLink($application, $text, $page, $linkUrl, $pageVar)
    {
        $linkUrl['params'] = array_merge($linkUrl['params'], array($pageVar => $page1));

        return $application->LinkTo($text, $linkUrl);
    }
}
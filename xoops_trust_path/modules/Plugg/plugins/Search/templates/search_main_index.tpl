<div id="plugg-search-results">
<?php $search_keywords = array_map('h', $search_keywords); $search_keywords_not = array_map('h', $search_keywords_not);?>
<h2><?php $this->_e('Search results');?></h2>
<dl class="search-data">
  <dt class="search-keywords"><?php $this->_e('Keywords:');?></dt>
  <dd class="search-keywords"><?php echo implode(', ', $search_keywords);?><?php if (!empty($search_keywords_not)):?>, -<?php echo implode(', -', $search_keywords_not);?><?php endif;?><?php if (!empty($search_keywords_failed)):?>, <del><?php echo implode('</del>, <del>', $search_keywords_failed);?></del><?php endif;?></dd>
</dl>
<?php if ($search_results->count()): $score_fmt = $search_has_score ? $this->_('(score: %d)') : '';?>
<div class="search-result">
  <dl>
<?php   foreach ($search_results as $result): $title = isset($result['title_html']) ? $result['title_html'] : h($result['title']);?>
    <dt><a href="<?php echo $this->Url(array('path' => sprintf('%d/%d', $result['searchable_id'], $result['content_id'])));?>"><?php echo $title;?></a> <span><?php printf($score_fmt, @$result['score']);?></span></dt>
    <dd class="search-result-summary"><p><?php echo $result['snippet_html'];?></p></dd>
    <dd class="search-result-data">
      <span><?php _h($searchables[$result['searchable_id']]['title']);?></span>
      <span> - </span>
      <span><?php echo $this->DateTime($result['created']);?></span>
<?php     if (!empty($result['author_id']) && isset($users[$result['author_id']])):?>
      <span> - </span>
      <span><?php echo $this->User_IdentityLink($users[$result['author_id']]);?></span>
<?php     endif;?>
    </dd>
<?php   endforeach;?>
  </dl>
  <div class="search-result-nav">
    <?php echo $this->PageNavRemote(
        'plugg-content',
        $search_pages,
        $search_page->getPageNumber(),
        array('params' => array(
            'order' => $search_order,
            'keyword' => $search_keywords_text,
            'keyword_type' => $search_keywords_type,
            'keyword_not' => $search_keywords_not_text,
            'p_' => $search_pages->getPlugins(),
            's_' => $search_pages->getSearchables()
        )) // link URL
    );?>
  </div>
</div>
<?php endif;?>
</div>
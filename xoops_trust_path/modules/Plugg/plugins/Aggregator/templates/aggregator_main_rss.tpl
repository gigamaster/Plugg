<?php echo'<?';?>xml version="1.0" encoding="utf-8" ?>
<rdf:RDF
  xmlns="http://purl.org/rss/1.0/"
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
  xml:lang="<?php echo SABAI_LANG;?>">
 <channel rdf:about="<?php echo $this->Url(array('path' => 'rss'));?>">
  <title><?php _h($this->PluginName());?> | <?php _h($this->SiteName());?></title>
  <link><?php echo $this->Url();?></link>
  <description><?php printf($this->_('Aggregation of blogs submitted by users at %s'), h($this->SiteName()));?></description>
  <items>
<?php if (isset($items) && $items->count()):?>
   <rdf:Seq>
<?php   foreach ($items as $item):?>
    <rdf:li rdf:resource="<?php _h($item->url);?>"/>
<?php   endforeach;?>
   </rdf:Seq>
<?php endif;?>
  </items>
 </channel>
<?php if ($items->count()):?>
<?php   foreach ($items as $item):?>
 <item rdf:about="<?php _h($item->url);?>">
  <title><?php _h($item->title);?></title>
  <link><?php _h($item->url);?></link>
  <description><?php _h($item->getSummary());?></description>
  <content:encoded><![CDATA[<?php echo $item->body;?>]]></content:encoded>
  <dc:creator><?php _h($item->author);?></dc:creator>
  <dc:date><?php echo date('Y-m-d\TH:iP', $item->published);?></dc:date>
<?php     if ($categories = $item->getCategories()):?>
<?php       foreach ($categories as $category):?>
  <dc:subject><?php _h($category);?></dc:subject>
<?php       endforeach;?>
<?php     endif;?>
 </item>
<?php   endforeach;?>
<?php endif;?>
</rdf:RDF>
<table class="plugg-vertical">
  <thead>
    <tr><td colspan="2"></td></tr>
  </thead>
  <tfoot>
    <tr>
      <td colspan="2"></td>
    </tr>
  </tfoot>
  <tbody>
    <tr>
      <th><?php $this->_e('Title');?></th>
      <td>
        <small><a href="<?php _h($feed->site_url);?>" title="<?php _h($feed->title);?>"><?php _h($feed->site_url);?></a></small><br />
<?php if ($feed->favicon_url && !$feed->favicon_hide):?>
        <img src="<?php _h($feed->favicon_url);?>" alt="" width="16" height="16" style="vertical-align:middle; margin-right:2px; padding:2px 0;" />
<?php endif;?>
<?php echo $this->LinkTo(h($feed->title), array('script' => 'main', 'base' => '/aggregator', 'path' => $feed->id));?>
      </td>
    </tr>
    <tr>
      <th><?php $this->_e('Description');?></th>
      <td><?php _h($feed->description);?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Owner');?></th>
      <td><?php if ($feed->user_id) echo $this->User_IdentityLink($feed->User);?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Language');?></th>
      <td><?php _h($feed->language);?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Status');?></th>
      <td><?php if ($feed->status == 1):?><?php $this->_e('Active');?><?php else:?><?php $this->_e('Pending');?><?php endif;?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Date added');?></th>
      <td><?php echo $this->DateTime($feed->created);?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Last published');?></th>
      <td><?php if ($feed->last_publish) echo $this->DateTime($feed->last_publish);?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Last fetched');?></th>
      <td><?php if ($feed->last_fetch) echo $this->DateTime($feed->last_fetch);?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Last pinged');?></th>
      <td><?php if ($feed->last_ping) echo $this->DateTime($feed->last_ping);?></td>
    </tr>
    <tr>
      <th><?php $this->_e('Articles');?></th>
      <td><?php echo $feed->item_count;?></td>
    </tr>
  </tbody>
</table>

<h3><?php $this->_e('Listing items');?></h3>
<div id="plugg-aggregator-admin-feeds-feed-items"></div>
<?php $this->ImportRemote('plugg-aggregator-admin-feeds-feed-items', $this->Url('/content/aggregator/feeds/' . $feed->id . '/items'));?>
<h3><?php $this->_e('Received friend requests');?></h3>
<div id="plugg-friends-requests-received"></div>
<?php $this->ImportRemote('plugg-friends-requests-received', array('path' => 'requests/received'));?>

<h3><?php $this->_e('Sent friend requests');?></h3>
<div id="plugg-friends-requests-sent"></div>
<?php $this->ImportRemote('plugg-friends-requests-sent', array('path' => 'requests/sent'));?>
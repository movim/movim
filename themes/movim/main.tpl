<?php /* -*- mode: html -*- */
?>
<?php $this->widget('Poller');?>
<?php $this->widget('Logout');?>
<div id="left">
  <?php $this->widget('Profile');?>
  <?php $this->widget('Friends');?>
  <?php $this->widget('Notifs');?>
  <?php $this->widget('Chat');?>
</div>
<div id="right">
<?php $this->widget('Log');?>
</div>
<div id="center">
  <?php $this->widget('Wall');?>
</div>

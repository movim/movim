<?php /* -*- mode: html -*- */
?>
<?php $this->widget('Poller');?>
<?php $this->widget('Logout');?>
<div id="topgray">
</div>
<div id="top">
  <?php $this->widget('Profile');?>
</div>
<div id="left">
    <?php $this->widget('Friends');?>
    <?php $this->widget('Notifs');?>
    <?php $this->widget('Chat');?>
</div>
<div id="right">
	<?php $this->widget('Log');?>
</div>
<div id="center">
  <h1>Configuration</h1>
  <?php $this->widget('Config');?>
</div>

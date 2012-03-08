<?php /* -*- mode: html -*- */
?>
<?php $this->widget('Poller');?>
<?php $this->widget('Logout');?>
<?php $this->widget('Chat');?>

<div id="left">
  <?php $this->widget('Profile');?>
  <?php $this->widget('Roster');?>
  <?php $this->widget('Notifs');?>
</div>
<div id="right">

</div>
<div id="center">
	<h1><?php echo t('Feed'); ?></h1>
    <?php $this->widget('Feed');?>
</div>

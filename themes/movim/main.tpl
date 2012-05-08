<?php /* -*- mode: html -*- */
?>
<?php $this->widget('Poller');?>
<?php $this->widget('Logout');?>
<?php $this->widget('Chat');?>

<div id="left">
  <?php $this->widget('Profile');?>
  <?php $this->widget('Notifs');?>
  <?php $this->widget('Roster');?>
</div>
<div id="right">

</div>
<div id="center" class="protect orange">
	<h1><?php echo t('Feed'); ?></h1>
    <?php $this->widget('Feed');?>
</div>

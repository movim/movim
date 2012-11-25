<?php /* -*- mode: html -*- */
?>
<?php $this->widget('Poller');?>
<?php $this->widget('Logout');?>
<?php $this->widget('Notifs');?>
<?php $this->widget('Chat');?>

<div id="head">
  <?php $this->widget('Profile');?>
</div>

<div id="main">
    <div id="left">
        <?php $this->widget('ProfileData');?>
    </div>
    <div id="center" class="protect black">
        <h1><?php echo t('Explore'); ?></h1>
        <?php $this->widget('Explore');?>
    </div>
</div>

<div id="right">
  <?php $this->widget('Roster');?>
</div>


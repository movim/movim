<?php /* -*- mode: html -*- */
?>
<?php $this->widget('Poller');?>
<?php $this->widget('Presence');?>
<?php $this->widget('Chat');?>
<?php $this->widget('ChatExt');?>

<div id="head">

</div>

<div id="main">
    <div id="left">
        <?php $this->widget('Profile');?>
        <?php $this->widget('Bookmark');?>
        <?php $this->widget('Notifs');?>
        <?php $this->widget('Location');?>
    </div>
    <div id="center">
        <div class="protect black" title="<?php echo getFlagTitle("black"); ?>"></div>
        <h1><?php echo t('Explore'); ?></h1>
        <?php $this->widget('Explore');?>
    </div>
</div>

<div id="right">
  <?php $this->widget('Roster');?>
</div>

<?php /* -*- mode: html -*- */
?>
<?php $this->widget('Poller');?>
<?php $this->widget('Presence');?>
<?php $this->widget('Chat');?>
<?php $this->widget('VisioExt');?>

<div id="main">
    <div id="left">
        <?php $this->widget('Profile');?>
        <?php $this->widget('Bookmark');?>
        <?php $this->widget('Notifs');?>
    </div>
    <div id="center">
        <div class="protect black" title="<?php echo getFlagTitle("black"); ?>"></div>
        <h1 class="paddedtopbottom"><i class="fa fa-globe"></i> <?php echo __('page.explore'); ?></h1>
        <?php $this->widget('Hot');?>
        <div class="clear"></div>
        <?php $this->widget('Explore');?>
    </div>
</div>

<div id="right">
  <?php $this->widget('Roster');?>
</div>

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

    </div>
    <div id="center">
      <h1><?php echo t('Help'); ?></h1>
      <?php $this->widget('Help');?>
    </div>
</div>

<div id="right">
    <?php $this->widget('Roster');?>
</div>



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
        <?php $this->widget('Connection');?>
        <?php $this->widget('Profile');?>
        <?php $this->widget('Bookmark');?>
        <?php $this->widget('Notifs');?>
        <?php $this->widget('Location');?>
    </div>
    <div id="center">
        <div title="<?php echo getFlagTitle("white"); ?>"  class="protect white"></div>
        <?php $this->widget('Tabs');?>
        <?php $this->widget('Config');?>
        <?php $this->widget('ConfigData');?>
        <?php $this->widget('PubsubSubscriptionConfig');?>
    </div>
</div>

<div id="right">
    <?php $this->widget('Roster');?>
</div>

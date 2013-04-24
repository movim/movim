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
        <?php $this->widget('Bookmark');?>
        <?php $this->widget('ProfileData');?>
    </div>
    <div id="center" class="protect white">
        <?php $this->widget('Tabs');?>
        <?php $this->widget('Config');?>
        <?php $this->widget('ConfigData');?>
        <?php $this->widget('PubsubSubscriptionConfig');?>
    </div>
</div>

<div id="right">
    <?php $this->widget('Roster');?>
</div>

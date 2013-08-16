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
        <?php $this->widget('ContactSummary');?>
        <?php $this->widget('ContactInfo');?>
        <?php $this->widget('ContactAction');?>
        <?php $this->widget('ContactManage');?>
    </div>

    <div id="center">
        <?php $this->widget('Tabs');?>
        <?php $this->widget('Wall');?>
        <?php $this->widget('ContactCard');?>
        <?php $this->widget('ContactPubsubSubscription');?>
    </div>
</div>

<div id="right">
    <?php $this->widget('Roster');?>
</div>

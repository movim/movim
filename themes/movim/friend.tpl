<?php /* -*- mode: html -*- */
?>

<?php $this->widget('Poller');?>
<?php $this->widget('Logout');?>
<?php $this->widget('Notifs');?>
<?php $this->widget('Chat');?>

<div id="head">
    <?php $this->widget('ContactSummary');?>
</div>

<div id="main">
    <div id="left">
        <?php $this->widget('ContactInfo');?>
    </div>

    <div id="center">
        <?php $this->widget('Tabs');?>
        <?php $this->widget('Wall');?>
        <?php $this->widget('ContactCard');?>
    </div>
</div>

<div id="right">
    <?php $this->widget('Roster');?>
</div>

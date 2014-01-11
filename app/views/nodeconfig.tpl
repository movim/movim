<?php /* -*- mode: html -*- */
?>

<?php $this->widget('Poller');?>
<?php $this->widget('Presence');?>
<?php $this->widget('Chat');?>
<?php $this->widget('VisioExt');?>

<div id="head">

</div>

<div id="main">
    <div id="left">
        <?php $this->widget('Profile');?>
        <?php $this->widget('Notifs');?>
        <?php $this->widget('Bookmark');?>
    </div>

    <?php $this->widget('Tabs');?>
    <div id="center">
        <?php $this->widget('NodeConfig');?>
        <?php $this->widget('NodeAffiliations');?>
        <?php $this->widget('NodeSubscriptions');?>
    </div>
</div>

<div id="right">
    <?php $this->widget('Roster');?>
</div>

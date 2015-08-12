<?php /* -*- mode: html -*- */
?>
<?php $this->widget('Presence');?>
<?php $this->widget('Chat');?>
<?php $this->widget('VisioExt');?>

<div id="main">
    <div id="left">
        <?php $this->widget('ContactSummary');?>
        <?php $this->widget('ContactInfo');?>
        <?php $this->widget('ContactAction');?>
        <div class="clear"></div>
        <?php $this->widget('ContactManage');?>
    </div>

    <?php $this->widget('Tabs');?>
    <div id="center">
        <?php $this->widget('Wall');?>
        <?php $this->widget('ContactCard');?>
        <?php $this->widget('ContactPubsubSubscription');?>
    </div>
</div>

<div id="right">
    <?php $this->widget('Roster');?>
</div>

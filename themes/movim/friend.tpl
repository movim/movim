<?php /* -*- mode: html -*- */
?>
<div id="topgray">

</div>

<?php $this->widget('Poller');?>
<?php $this->widget('Logout');?>
<?php $this->widget('Chat');?>
<div id="top">
<?php $this->widget('Friendinfos');?>
</div>
<div id="left">
    <?php /*$this->widget('Profile');*/?>
    <?php $this->widget('Friends');?>
    <?php $this->widget('Notifs');?>
</div>
<div id="right">
    <?php $this->widget('Log');?>
</div>
<div id="center">
    <?php $this->widget('Tabs');?>
    <?php $this->widget('Wall');?>
    <?php $this->widget('Friendvcard');?>
</div>

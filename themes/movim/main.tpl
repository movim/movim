<?php /* -*- mode: html -*- */
?>
<?php $this->widget('Poller');?>
<?php $this->widget('Logout');?>
<div id="topgray">
</div>
<div id="top">
  <?php $this->widget('Profile');?>
</div>
<div id="left">
  <?php $this->widget('Friends');?>
  <?php $this->widget('Notifs');?>
  <?php $this->widget('Chat');?>
</div>
<div id="right">
<?php $this->widget('Log');?>
</div>
<div id="center">
    <?php $this->widget('Tabs');?>
    <?php /*$this->widget('Forums');*/?>
    <?php /*$this->widget('Wall');*/?>
    <?php $this->widget('Vcard');?>
</div>

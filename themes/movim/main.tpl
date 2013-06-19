<?php /* -*- mode: html -*- */
?>
<?php $this->widget('Poller');?>
<?php $this->widget('Logout');?>
<?php $this->widget('Notifs');?>
<?php $this->widget('Chat');?>
<?php $this->widget('ChatExt');?>
    

<div id="main">    
    <div id="left">
        <?php $this->widget('Profile');?>
        <?php $this->widget('Bookmark');?>
        <?php $this->widget('ProfileData');?>
    </div>

    <div id="center">
        <?php $this->widget('Feed');?>
    </div>

</div>

<div id="right">
  <?php $this->widget('Roster');?>
</div>
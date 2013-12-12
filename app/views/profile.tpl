<?php /* -*- mode: html -*- */
?>

<?php $this->widget('Poller');?>
<?php $this->widget('Presence');?>
<?php $this->widget('Chat');?>
<?php //$this->widget('ChatExt');?>
    
<div id="head">
</div>
<div id="main">
    <div id="left">
        <?php $this->widget('Connection');?>
        <?php $this->widget('Profile');?>
        <?php $this->widget('Bookmark');?>
        <?php $this->widget('Notifs');?>
    </div>

    <?php $this->widget('Tabs');?>
    
    <div id="center">
        <?php $this->widget('Vcard4');?>
        <?php $this->widget('Avatar');?>
        <?php $this->widget('Location');?>
    </div>
</div>

<div id="right">
    <?php $this->widget('Roster');?>
</div>

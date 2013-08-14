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
        <h1><?php echo t('Edit my profile'); ?></h1>
        <?php $this->widget('Vcard');?>
    </div>
</div>

<div id="right">
    <?php $this->widget('Roster');?>
</div>

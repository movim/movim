<?php /* -*- mode: html -*- */
?>

<?php $this->widget('Poller');?>
<?php $this->widget('Presence');?>
<?php $this->widget('Chat');?>
<?php $this->widget('VisioExt');?>

<div id="main">
    <div id="left">
        <?php $this->widget('Connection');?>
        <?php $this->widget('Profile');?>
        <?php $this->widget('Bookmark');?>
        <?php $this->widget('Notifs');?>
    </div>

    <div id="center">
        <div title="<?php echo getFlagTitle("white"); ?>" class="protect white"></div>
        <?php $this->widget('Tabs');?>
        <?php $this->widget('Media');?>
        <?php $this->widget('MediaUpload');?>
    </div>
</div>

<div id="right">
    <?php $this->widget('Roster');?>
</div>

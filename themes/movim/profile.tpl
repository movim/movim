<?php /* -*- mode: html -*- */
?>

<?php $this->widget('Poller');?>
<?php $this->widget('Logout');?>
<?php $this->widget('Chat');?>
<?php /*$this->widget('Log');*/?>
    
<div id="left">
    <?php $this->widget('Profile');?>
    <?php $this->widget('Roster');?>
</div>
<div id="right">
    <?php $this->widget('Notifs');?>
</div>
<div id="center">
	<h1><?php echo t('Profile'); ?></h1>
    <?php $this->widget('Vcard');?>
</div>

<?php $this->widget('Search');?>
<?php $this->widget('Upload');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>
<?php $this->widget('Tabs');?>
<?php if($this->user?->hasOMEMO()) $this->widget('ChatOmemo');?>
<?php $this->widget('Visio');?>

<?php $this->widget('ContactActions');?>
<?php $this->widget('AdHoc');?>
<?php $this->widget('PostActions');?>

<nav aria-label="<?php echo __('global.main_menu') ?>" class="on_desktop">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Shortcuts');?>
    <?php $this->widget('SpacesMenu');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <?php $this->widget('Post');?>
</main>

<?php if ($this->user?->hasPubsub() && $this->user?->hasUpload()) { ?>
    <?php $this->widget('PublishStories');?>
<?php } ?>

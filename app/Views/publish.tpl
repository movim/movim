<?php $this->widget('Search');?>
<?php $this->widget('Upload'); ?>
<?php $this->widget('Notifications');?>
<?php if($this->user?->hasOMEMO()) $this->widget('ChatOmemo');?>
<?php $this->widget('Visio');?>

<nav aria-label="<?php echo __('global.main_menu') ?>" class="on_desktop">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Shortcuts');?>
    <?php $this->widget('SpacesMenu');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <section id="sidebar">
        <?php $this->widget('PublishHelp');?>
    </section>
    <?php $this->widget('Publish');?>
</main>

<?php if ($this->user?->hasUpload()) { ?>
    <?php $this->widget('Snap');?>
    <?php $this->widget('Draw');?>
    <?php if ($this->user?->hasPubsub()) { ?>
        <?php $this->widget('PublishStories');?>
    <?php } ?>
<?php } ?>
<?php $this->widget('Search');?>
<?php $this->widget('PostActions');?>
<?php $this->widget('ContactActions');?>
<?php $this->widget('AdHoc');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>
<?php $this->widget('Tabs');?>
<?php $this->widget('Upload');?>
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
        <?php $this->widget('ContactData'); ?>
        <?php $this->widget('ContactSubscriptions'); ?>
    </section>
    <div>
        <?php $this->widget('ContactHeader'); ?>
        <?php $this->widget('ContactBlogConfig'); ?>
        <?php $this->widget('CommunityPosts'); ?>
    </div>
</main>

<?php if ($this->user?->hasPubsub() && $this->user?->hasUpload()) { ?>
    <?php $this->widget('PublishStories');?>
    <?php $this->widget('StoriesViewer');?>
<?php } ?>
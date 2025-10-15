<?php $this->widget('Search');?>
<?php $this->widget('PostActions');?>
<?php $this->widget('ContactActions');?>
<?php $this->widget('AdHoc');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>
<?php $this->widget('Tabs');?>
<?php $this->widget('Upload');?>
<?php if(me()->hasOMEMO()) $this->widget('ChatOmemo');?>

<nav>
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <aside>
        <?php $this->widget('ContactData'); ?>
        <?php $this->widget('ContactSubscriptions'); ?>
    </aside>
    <div>
        <?php $this->widget('ContactHeader'); ?>
        <?php $this->widget('ContactBlogConfig'); ?>
        <?php $this->widget('CommunityPosts'); ?>
    </div>
</main>

<?php if (me()->hasPubsub() && me()->hasUpload()) { ?>
    <?php $this->widget('PublishStories');?>
    <?php $this->widget('StoriesViewer');?>
<?php } ?>
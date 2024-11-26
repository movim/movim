<?php $this->widget('Search');?>
<?php $this->widget('PostActions');?>
<?php $this->widget('ContactActions');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>
<?php $this->widget('Tabs');?>
<?php if(\App\User::me()->hasOMEMO()) $this->widget('ChatOmemo');?>
<?php $this->widget('Location');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main style="background-color: rgb(var(--movim-background));">
    <aside>
        <?php $this->widget('ContactData'); ?>
        <?php $this->widget('ContactSubscriptions'); ?>
        <?php $this->widget('AdHoc'); ?>
    </aside>
    <div>
        <?php $this->widget('ContactHeader'); ?>
        <?php $this->widget('CommunityPosts'); ?>
    </div>
</main>

<?php if (\App\User::me()->hasPubsub()) { ?>
    <?php $this->widget('PublishStories');?>
    <?php $this->widget('StoriesViewer');?>
<?php } ?>
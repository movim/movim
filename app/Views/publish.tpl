<?php $this->widget('Search');?>
<?php $this->widget('Upload'); ?>
<?php $this->widget('Notifications');?>
<?php if(me()->hasOMEMO()) $this->widget('ChatOmemo');?>

<nav>
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <aside>
        <?php $this->widget('PublishHelp');?>
    </aside>
    <div>
        <?php $this->widget('Publish');?>
    </div>
</main>

<?php $this->widget('Snap');?>
<?php $this->widget('Draw');?>
<?php $this->widget('PublishStories');?>

<?php $this->widget('Search');?>
<?php $this->widget('Upload');?>
<?php $this->widget('Onboarding');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>
<?php if(me()->hasOMEMO()) $this->widget('ChatOmemo');?>

<?php $this->widget('PostActions');?>

<nav>
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <aside>
        <?php $this->widget('NewsNav');?>
    </aside>
    <div>
        <?php $this->widget('Menu');?>
    </div>
</main>

<?php if (me()->hasUpload()) { ?>
    <?php $this->widget('Snap');?>
    <?php $this->widget('Draw');?>

    <?php if (me()->hasPubsub()) { ?>
        <?php $this->widget('PublishStories');?>
    <?php } ?>
<?php } ?>

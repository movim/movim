<?php $this->widget('Search');?>
<?php $this->widget('Upload');?>
<?php $this->widget('Onboarding');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>
<?php if($this->user?->hasOMEMO()) $this->widget('ChatOmemo');?>

<?php $this->widget('PostActions');?>

<nav>
    <?php $this->widget('Presence');?>
    <?php $this->widget('Shortcuts');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <aside>
        <?php $this->widget('ContactsSuggestions');?>
        <?php $this->widget('NewsNav');?>
    </aside>
    <div>
        <?php $this->widget('Menu');?>
    </div>
</main>

<?php if ($this->user?->hasUpload()) { ?>
    <?php $this->widget('Snap');?>
    <?php $this->widget('Draw');?>

    <?php if ($this->user?->hasPubsub()) { ?>
        <?php $this->widget('PublishStories');?>
    <?php } ?>
<?php } ?>

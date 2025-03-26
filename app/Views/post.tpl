<?php $this->widget('Search');?>
<?php $this->widget('Upload');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>
<?php $this->widget('Tabs');?>
<?php if(\App\User::me()->hasOMEMO()) $this->widget('ChatOmemo');?>
<?php $this->widget('Location');?>

<?php $this->widget('ContactActions');?>
<?php $this->widget('PostActions');?>

<nav>
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <?php $this->widget('Post');?>
</main>

<?php $this->widget('PublishStories');?>
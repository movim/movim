<?php $this->widget('Search');?>
<?php $this->widget('Notification');?>
<?php $this->widget('Toast');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Upload'); ?>
<?php $this->widget('Notifications');?>
<?php $this->widget('Location');?>
<?php $this->widget('ChatOmemo');?>
<?php $this->widget('Location');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main style="background-color: rgb(var(--movim-background));">
    <aside>
        <?php $this->widget('PublishHelp');?>
    </aside>
    <div>
        <?php $this->widget('Publish');?>
    </div>
</main>

<?php $this->widget('Snap');?>
<?php $this->widget('Draw');?>
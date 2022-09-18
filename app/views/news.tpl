<?php $this->widget('Init');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Notification');?>
<?php $this->widget('Toast');?>
<?php $this->widget('Upload');?>
<?php $this->widget('Search');?>
<?php $this->widget('Onboarding');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>
<?php $this->widget('ChatOmemo');?>
<?php $this->widget('Location');?>

<?php $this->widget('PostActions');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main style="background-color: rgb(var(--movim-background));">
    <aside>
        <?php $this->widget('NewsNav');?>
    </aside>
    <div>
        <?php $this->widget('Menu');?>
    </div>
</main>

<?php $this->widget('Snap');?>
<?php $this->widget('Draw');?>
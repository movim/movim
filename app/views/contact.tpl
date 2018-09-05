<?php $this->widget('Notification');?>
<?php $this->widget('Search');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('PostActions');?>
<?php $this->widget('ContactActions');?>
<?php $this->widget('Invitations');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main style="background-color: var(--movim-background);">
    <aside>
        <?php $this->widget('ContactData'); ?>
        <?php $this->widget('AdHoc'); ?>
    </aside>
    <div>
        <?php $this->widget('ContactHeader'); ?>
        <?php $this->widget('CommunityPosts'); ?>
    </div>
</main>

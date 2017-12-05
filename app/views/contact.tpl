<?php $this->widget('Notification');?>
<?php $this->widget('Search');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('PostActions');?>
<?php $this->widget('ContactActions');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <section style="background-color: var(--movim-background);">
        <?php if(empty($_GET['s'])) { ?>
            <aside>
                <?php $this->widget('ContactDisco');?>
            </aside>
            <div>
                <?php $this->widget('Invitations');?>
                <?php $this->widget('Roster');?>
            </div>
        <?php } else { ?>
            <aside>
                <?php $this->widget('ContactData'); ?>
                <?php $this->widget('AdHoc'); ?>
            </aside>
            <div>
                <?php $this->widget('ContactHeader'); ?>
                <?php $this->widget('CommunityPosts'); ?>
            </div>
        <?php } ?>
    </section>
</main>

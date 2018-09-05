<?php $this->widget('Search');?>
<?php $this->widget('Notification');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Upload'); ?>
<?php $this->widget('Invitations');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main style="background-color: var(--movim-background);">
    <aside>
        <?php $this->widget('PublishHelp');?>
    </aside>
    <div>
        <?php $this->widget('PublishBrief');?>
    </div>
</main>

<?php $this->widget('Search');?>
<?php $this->widget('Notification');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Upload'); ?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <section style="background-color: var(--movim-background);">
       <aside>
            <?php $this->widget('PublishHelp');?>
        </aside>
        <?php $this->widget('Publish');?>
    </section>
</main>

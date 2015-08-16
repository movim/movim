<?php $this->widget('Upload'); ?>

<nav class="color dark">
    <?php $this->widget('Navigation');?>
    <?php $this->widget('Presence');?>
</nav>

<main>
    <?php $this->widget('Header'); ?>
    <section>
        <?php $this->widget('Groups'); ?>
        <?php $this->widget('Group'); ?>
        <?php $this->widget('Publish'); ?>
    </section>
</main>

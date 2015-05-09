<?php $this->widget('Init');?>

<nav class="color dark">
    <?php $this->widget('Navigation');?>
    <?php $this->widget('Presence');?>
</nav>

<main>
    <?php $this->widget('Header');?>
    <section>
        <?php $this->widget('Menu');?>
        <?php $this->widget('Post');?>
        <?php $this->widget('Publish');?>
    </section>
</main>

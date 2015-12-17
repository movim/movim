<?php $this->widget('Init');?>
<?php $this->widget('Upload');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <?php //$this->widget('Header');?>
    <section>
        <?php $this->widget('Menu');?>
        <?php $this->widget('Post');?>
        <?php $this->widget('Publish');?>
    </section>
</main>

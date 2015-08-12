<?php $this->widget('Presence'); ?>
<?php $this->widget('LoginAnonymous'); ?>
<main>
    <?php $this->widget('Header');?>
    <section>
        <div>
            <?php $this->widget('Rooms'); ?>
        </div>
        <?php $this->widget('Chat'); ?>
    </section>
</main>

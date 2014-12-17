<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <?php $this->widget('Header'); ?>
    <section>
        <?php $this->widget('Roster');?>
        <div>
            <?php $this->widget('Chat');?>
        </div>
    </section>
</main>

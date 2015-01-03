<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <?php $this->widget('Header'); ?>
    <section>
        <div>
            <?php $this->widget('Notifs');?>
            <?php $this->widget('Roster');?>
        </div>
        <div>
            <?php $this->widget('Tabs');?>
            <?php $this->widget('Contact');?>
        </div>
    </section>
</main>

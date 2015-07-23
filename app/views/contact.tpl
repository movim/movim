<nav class="color dark">
    <?php $this->widget('Navigation');?>
    <?php $this->widget('Presence');?>
</nav>

<main>
    <?php $this->widget('Header'); ?>
    <section>
        <div>
            <?php $this->widget('Notifs');?>
            <?php $this->widget('Roster');?>
        </div>
        <div id="contact_widget" class="card shadow">
            <?php $this->widget('Contact');?>
        </div>
    </section>
</main>

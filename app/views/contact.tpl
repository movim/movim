<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <?php //$this->widget('Header'); ?>
    <section>
        <div style="background-color: #EEE;">
            <?php $this->widget('Notifs');?>
            <?php $this->widget('Roster');?>
        </div>
        <div id="contact_widget">
            <?php $this->widget('Contact');?>
        </div>
    </section>
</main>

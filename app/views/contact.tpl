<?php $this->widget('Notification');?>
<?php $this->widget('Search');?>
<?php $this->widget('VisioLink');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <section>
        <div style="background-color: #EEE;">
            <?php $this->widget('Invitations');?>
            <?php $this->widget('Roster');?>
        </div>
        <div id="contact_widget" class="spin">
            <?php $this->widget('Contact');?>
        </div>
    </section>
</main>

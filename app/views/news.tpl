<?php $this->widget('Init');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Notification');?>
<?php $this->widget('Upload');?>
<?php $this->widget('Search');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <section style="background-color: #EEE;">
        <aside>
            <?php $this->widget('Notifs');?>
            <?php $this->widget('NewsNav');?>
        </aside>
        <?php $this->widget('Menu');?>
    </section>
</main>

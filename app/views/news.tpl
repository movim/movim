<?php $this->widget('Init');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Notification');?>
<?php $this->widget('Upload');?>
<?php $this->widget('Search');?>
<?php $this->widget('Onboarding');?>

<?php $this->widget('PostActions');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <section style="background-color: #EEE;">
        <aside>
            <?php $this->widget('Notifs');?>
            <?php $this->widget('NewsNav');?>
        </aside>
        <div>
        <?php $this->widget('PublishBrief');?>
        <?php $this->widget('Menu');?>
        </div>
    </section>
</main>

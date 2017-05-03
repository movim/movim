<?php if($this->user->isLogged()) { ?>
    <?php $this->widget('Search');?>
    <?php $this->widget('Notification');?>
    <?php $this->widget('VisioLink');?>

    <nav class="color dark">
        <?php $this->widget('Presence');?>
        <?php $this->widget('Navigation');?>
    </nav>
<?php } ?>

<main style="background-color: #EEE;">
    <section>
        <?php if($this->user->isLogged()) { ?>
            <aside>
                <?php $this->widget('NewsNav');?>
            </aside>
        <?php } ?>
        <div>
            <?php $this->widget('Blog');?>
        </div>
    </section>
</main>

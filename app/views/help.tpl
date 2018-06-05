<?php $this->widget('Search');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Notification');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <section>
        <div>
            <header>
                <ul class="list middle">
                    <li>
                        <span id="menu" class="primary icon active gray">
                            <i class="material-icons on_desktop">help</i>
                            <i class="material-icons on_mobile" onclick="MovimTpl.toggleMenu()">menu</i>
                        </span>
                        <p class="center"><?php echo __('page.help'); ?></p>
                    </li>
                </ul>
            </header>
            <?php $this->widget('Tabs');?>
            <?php $this->widget('Help');?>
            <?php $this->widget('About');?>
        </div>
    </section>
</main>

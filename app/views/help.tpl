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
                        <span id="menu" class="primary on_mobile icon active gray" onclick="MovimTpl.toggleMenu()"><i class="zmdi zmdi-menu"></i></span>
                        <span class="primary on_desktop icon gray"><i class="zmdi zmdi-help"></i></span>
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

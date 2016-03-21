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
                        <span class="primary on_desktop icon gray"><i class="zmdi zmdi-settings"></i></span>
                        <p class="center"><?php echo __('page.configuration'); ?></p>
                    </li>
                </ul>
            </header>
            <?php $this->widget('Tabs');?>
            <?php $this->widget('Vcard4');?>
            <?php $this->widget('Avatar');?>
            <?php $this->widget('Config');?>
            <?php $this->widget('Account');?>
            <?php $this->widget('AdHoc');?>
        </div>
    </section>
</main>

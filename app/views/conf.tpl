<?php $this->widget('Search');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Notification');?>
<?php $this->widget('Onboarding');?>

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
                        <span id="menu" class="primary icon active gray" >
                            <i class="zmdi zmdi-menu on_mobile" onclick="MovimTpl.toggleMenu()"></i>
                            <i class="zmdi zmdi-settings on_desktop"></i>
                        </span>
                        <p class="center"><?php echo __('page.configuration'); ?></p>
                    </li>
                </ul>
            </header>

            <?php $this->widget('Tabs');?>
            <?php $this->widget('Vcard4');?>
            <?php if($this->user->isSupported('pubsub')) { ?>
                <?php $this->widget('Avatar');?>
                <?php $this->widget('Config');?>
            <?php } ?>
            <?php $this->widget('Account');?>
            <?php $this->widget('AdHoc');?>
        </div>
    </section>
</main>

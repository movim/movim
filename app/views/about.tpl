<main>
    <section>
        <div>
            <header>
                <ul class="list middle">
                    <li>

                        <span class="primary active icon gray">
                            <a href="<?php echo Route::urlize('main'); ?>">
                                <i class="zmdi zmdi-arrow-left"></i>
                            </a>
                        </span>
                        <p class="center"><?php echo __('page.about'); ?></p>
                    </li>
                </ul>
            </header>
            <?php $this->widget('Tabs');?>

            <?php $this->widget('About');?>
            <?php $this->widget('Help');?>
            <?php $this->widget('Caps');?>
        </div>
    </section>
</main>

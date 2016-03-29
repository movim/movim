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
                        <p class="center"><?php echo __('page.administration'); ?></p>
                    </li>
                </ul>
            </header>
            <?php $this->widget('AdminLogin');?>
        </div>
    </section>
</main>

<main>
    <section>
        <div>
            <header>
                <ul class="list middle">
                    <li>

                        <span class="primary active icon gray">
                            <a href="<?php echo Route::urlize('main'); ?>">
                                <i class="zmdi zmdi-home"></i>
                            </a>
                        </span>
                        <p class="center"><?php echo __('page.account_creation'); ?></p>
                    </li>
                </ul>
            </header>
            <?php $this->widget('Subscribe');?>
        </div>
    </section>
</main>

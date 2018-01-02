<?php $this->widget('Notification');?>

<main>
    <section>
        <div>
            <header>
                <ul class="list middle">
                    <li>
                        <span class="primary active icon gray">
                            <a href="<?php echo \Movim\Route::urlize('main'); ?>">
                                <i class="zmdi zmdi-arrow-left"></i>
                            </a>
                        </span>
                        <p>
                        <a class="button color" href="<?php echo \Movim\Route::urlize('login'); ?>">
                            <?php echo __('page.login'); ?>
                        </a>
                        <a class="button flat" href="<?php echo \Movim\Route::urlize('account'); ?>">
                            <?php echo __('button.register'); ?>
                        </a>
                        </p>
                    </li>
                </ul>
            </header>
            <?php $this->widget('Communities'); ?>
        </div>
    </section>
</main>

<main>
    <div>
        <header>
            <ul class="list middle">
                <li>
                    <span class="primary active icon gray">
                        <a href="<?php echo \Movim\Route::urlize('main'); ?>">
                            <i class="material-icons">arrow_back</i>
                        </a>
                    </span>
                    <p class="center"><?php echo __('page.administration'); ?></p>
                </li>
            </ul>
        </header>
        <?php $this->widget('AdminLogin');?>
    </div>
</main>

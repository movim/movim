<main>
    <div class="large">
        <header>
            <ul class="list middle">
                <li>
                    <span class="primary active icon gray">
                        <a href="<?php echo \Movim\Route::urlize('main'); ?>">
                            <i class="material-icons">arrow_back</i>
                        </a>
                    </span>
                    <content>
                        <p class="center"><?php echo __('page.about'); ?></p>
                    </content>
                </li>
            </ul>
        </header>
        <?php $this->widget('Tabs');?>

        <?php $this->widget('About');?>
        <?php $this->widget('Help');?>
        <?php $this->widget('Caps');?>
        <?php $this->widget('Statistics');?>
    </div>
</main>

<main>
    <div>
        <header>
            <ul class="list middle">
                <li>
                    <span class="primary active icon gray" onclick="MovimUtils.redirect('<?php echo \Movim\Route::urlize('login') ?>')">
                        <i class="material-symbols">arrow_back</i>
                    </span>
                    <div>
                        <p><?php echo __('page.account_creation'); ?></p>
                    </div>
                </li>
            </ul>
        </header>
        <?php $this->widget('Subscribe');?>
    </div>
</main>

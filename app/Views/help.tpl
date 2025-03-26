<?php $this->widget('Search');?>
<?php $this->widget('Notifications');?>
<?php if(\App\User::me()->hasOMEMO()) $this->widget('ChatOmemo');?>
<?php $this->widget('Location');?>

<nav>
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <div>
        <header>
            <ul class="list middle">
                <li>
                    <span id="menu" class="primary icon gray">
                        <i class="material-symbols">help</i>
                    </span>
                    <div>
                        <p><?php echo __('page.help'); ?></p>
                    </div>
                </li>
            </ul>
        </header>
        <?php $this->widget('Tabs');?>
        <ul class="tabs" id="navtabs"></ul>
        <?php $this->widget('Help');?>
        <?php $this->widget('About');?>
    </div>
</main>

<?php if (\App\User::me()->hasPubsub()) { ?>
    <?php $this->widget('PublishStories');?>
<?php } ?>
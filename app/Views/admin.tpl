<?php $this->widget('Search');?>
<?php $this->widget('Onboarding');?>
<?php $this->widget('Notifications');?>

<nav>
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <div>
        <header class="on_desktop">
            <ul class="list middle">
                <li>
                    <span id="menu" class="primary icon gray" >
                        <i class="material-symbols">manage_accounts</i>
                    </span>
                    <div>
                        <p><?php echo __('page.administration'); ?></p>
                    </div>
                </li>
            </ul>
        </header>

        <?php if (me()->admin) { ?>
            <?php $this->widget('Tabs');?>
            <ul class="tabs" id="navtabs"></ul>

            <?php $this->widget('AdminMain');?>
            <?php $this->widget('AdminSessions');?>
            <?php $this->widget('AdminReported');?>
        <?php } ?>
    </div>
</main>

<?php if (me()->hasPubsub()) { ?>
    <?php $this->widget('Upload');?>
    <?php $this->widget('PublishStories');?>
<?php } ?>
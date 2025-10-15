<?php $this->widget('Search');?>
<?php $this->widget('Upload');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>

<nav>
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main style="background-color: rgb(var(--movim-background))">
    <div class="large">
        <header>
            <ul class="list middle">
                <li>
                    <div>
                        <p class="line center"><?php echo __('communityaffiliation.subscriptions'); ?></p>
                        <p class="line center"><?php echo __('communityaffiliation.subscriptions_text'); ?></p>
                    </div>
                </li>
            </ul>
        </header>
        <?php if (me()->hasPubsub()) { ?>
            <?php $this->widget('CommunitySubscriptions'); ?>
        <?php } ?>
    </div>
</main>

<?php if (me()->hasPubsub() && me()->hasUpload()) { ?>
    <?php $this->widget('PublishStories');?>
<?php } ?>
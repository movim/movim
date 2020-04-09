<?php $this->widget('Search');?>
<?php $this->widget('Notification');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Upload'); ?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>

<?php $this->widget('PostActions');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main style="background-color: var(--movim-background)">
    <?php if (empty($_GET['s'])) { ?>
        <div class="large">
            <header>
                <ul class="list middle">
                    <li>
                        <div>
                            <p class="center"><?php echo __('page.communities'); ?></p>
                            <p class="center line"><?php echo __('communities.empty_text'); ?></p>
                        </div>
                    </li>
                </ul>
            </header>
            <?php $this->widget('Tabs');?>
            <?php $this->widget('Communities'); ?>
            <?php if (\App\User::me()->hasPubsub()) { ?>
                <?php $this->widget('CommunitySubscriptions'); ?>
            <?php } ?>
            <?php $this->widget('CommunitiesServers'); ?>
        </div>
    <?php } elseif (empty($_GET['n'])) { ?>
        <aside>
            <?php $this->widget('CommunitiesServerInfo'); ?>
            <?php $this->widget('NewsNav');?>
        </aside>
        <?php $this->widget('CommunitiesServer'); ?>

    <?php } else { ?>
        <aside>
            <?php $this->widget('CommunityData'); ?>
            <?php $this->widget('CommunityConfig'); ?>
            <?php $this->widget('CommunityAffiliations'); ?>
        </aside>
        <div id="community">
        <?php $this->widget('CommunityHeader'); ?>
        <?php $this->widget('CommunityPosts'); ?>
        </div>
    <?php } ?>
</main>

<?php $this->widget('Search');?>
<?php $this->widget('Notification');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Upload'); ?>

<?php $this->widget('PostActions');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <section style="background-color: #EEE;">
        <?php if(empty($_GET['s'])) { ?>
            <div>
                <header>
                    <ul class="list middle">
                        <li>
                            <?php if($this->user->isSupported('pubsub')) { ?>
                                <span
                                    class="control icon gray active"
                                    onclick="MovimUtils.redirect('<?php echo \Movim\Route::urlize('community', 'subscriptions'); ?>')">
                                    <i class="zmdi zmdi-settings"></i>
                                </span>
                            <?php } ?>
                            <p class="center"><?php echo __('page.communities'); ?></p>
                            <p class="center line"><?php echo __('communities.empty_text'); ?></p>
                        </li>
                    </ul>
                </header>
                <?php $this->widget('Tabs');?>
                <?php $this->widget('CommunitiesDiscover'); ?>
                <?php $this->widget('Communities'); ?>
                <?php $this->widget('CommunitiesServers'); ?>
            </div>
        <?php } elseif($_GET['s'] == 'subscriptions'
                    && $this->user->isSupported('pubsub')) { ?>
            <aside>
                <?php $this->widget('NewsNav');?>
            </aside>
            <?php $this->widget('CommunitySubscriptions'); ?>

        <?php } elseif(empty($_GET['n'])) { ?>
            <aside>
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
    </section>
</main>

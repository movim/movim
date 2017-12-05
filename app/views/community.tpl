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
    <section style="background-color: var(--movim-background)">
        <?php if(empty($_GET['s'])) { ?>
            <div>
                <header>
                    <ul class="list middle">
                        <li>
                            <p class="center"><?php echo __('page.communities'); ?></p>
                            <p class="center line"><?php echo __('communities.empty_text'); ?></p>
                        </li>
                    </ul>
                </header>
                <?php $this->widget('Tabs');?>
                <?php $this->widget('Communities'); ?>
                <?php $this->widget('CommunitiesServers'); ?>
                <?php if($this->user->isSupported('pubsub')) { ?>
                    <?php $this->widget('CommunitySubscriptions'); ?>
                <?php } ?>
            </div>
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

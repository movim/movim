<?php if ($this->user) { ?>
    <?php $this->widget('Search');?>
    <?php $this->widget('Upload'); ?>
    <?php $this->widget('Notifications');?>
    <?php $this->widget('SendTo');?>
    <?php if($this->user?->hasOMEMO()) $this->widget('ChatOmemo');?>
    <?php $this->widget('PostActions');?>
    <?php $this->widget('Visio');?>

    <nav aria-label="<?php echo __('global.main_menu') ?>" class="on_desktop">
        <?php $this->widget('Presence');?>
        <?php $this->widget('Shortcuts');?>
        <?php $this->widget('SpacesMenu');?>
        <?php $this->widget('Navigation');?>
    </nav>

    <?php $this->widget('BottomNavigation');?>
<?php } ?>

<main style="background-color: rgb(var(--movim-background))">
    <?php if (!$this->user) { ?>
        <section id="sidebar">
            <?php $this->widget('CommunityDataPublic'); ?>
        </section>
        <div>
            <?php $this->widget('PublicNavigation');?>
            <hr />
            <?php $this->widget('Blog');?>
            <footer>
                <ul class="list">
                    <li>
                        <span class="primary icon gray">
                            <i class="material-symbols">cloud_queue</i>
                        </span>
                        <div>
                            <p class="center"><a target="_blank" href="https://movim.eu">Powered by Movim</a></p>
                        </div>
                    </li>
                </ul>
            </footer>
        </div>
    <?php } else { ?>
        <?php if (empty($_GET['n'])) { ?>
            <section id="sidebar">
                <?php $this->widget('CommunitiesTags'); ?>
                <?php $this->widget('NewsNav');?>
                <?php $this->widget('CommunitiesServerInfo'); ?>
            </section>
            <div>
                <?php $this->widget('CommunitiesServer'); ?>
            </div>
        <?php } else { ?>
            <section id="sidebar">
                <?php $this->widget('CommunityData'); ?>
                <?php $this->widget('CommunityConfig'); ?>
                <?php $this->widget('CommunityAffiliations'); ?>
            </section>
            <div id="community">
            <?php $this->widget('CommunityHeader'); ?>
            <?php $this->widget('CommunityPosts'); ?>
            </div>
        <?php } ?>
    <?php } ?>
</main>

<?php if ($this->user && $this->user?->hasPubsub() && $this->user?->hasUpload()) { ?>
    <?php $this->widget('PublishStories');?>
<?php } ?>

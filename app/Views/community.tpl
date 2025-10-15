<?php if (isLogged()) { ?>
    <?php $this->widget('Search');?>
    <?php $this->widget('Upload'); ?>
    <?php $this->widget('Notifications');?>
    <?php $this->widget('SendTo');?>
    <?php if(me()->hasOMEMO()) $this->widget('ChatOmemo');?>
    <?php $this->widget('PostActions');?>

    <nav>
        <?php $this->widget('Presence');?>
        <?php $this->widget('Navigation');?>
    </nav>

    <?php $this->widget('BottomNavigation');?>
<?php } ?>

<main style="background-color: rgb(var(--movim-background))">
    <?php if (!isLogged()) { ?>
        <aside>
            <?php $this->widget('CommunityDataPublic'); ?>
        </aside>
        <div>
            <?php $this->widget('PublicNavigation');?>
            <hr />
            <?php $this->widget('Blog');?>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">cloud_queue</i>
                    </span>
                    <div>
                        <p class="center normal"><a target="_blank" href="https://movim.eu">Powered by Movim</a></p>
                    </div>
                </li>
            </ul>
        </div>
    <?php } else { ?>
        <?php if (empty($_GET['n'])) { ?>
            <aside>
                <?php $this->widget('CommunitiesTags'); ?>
                <?php $this->widget('NewsNav');?>
                <?php $this->widget('CommunitiesServerInfo'); ?>
            </aside>
            <div>
                <?php $this->widget('CommunitiesServer'); ?>
            </div>
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
    <?php } ?>
</main>

<?php if (isLogged() && me()->hasPubsub() && me()->hasUpload()) { ?>
    <?php $this->widget('PublishStories');?>
<?php } ?>

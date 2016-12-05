<?php $this->widget('Search');?>
<?php $this->widget('Notification');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Upload'); ?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <section style="background-color: #EEE;">
        <?php if(empty($_GET['s'])) { ?>
            <aside>
                <?php $this->widget('NewsNav');?>
            </aside>
            <?php $this->widget('Communities'); ?>
        <?php } elseif(empty($_GET['n'])) { ?>
            <aside>
                <?php $this->widget('NewsNav');?>
            </aside>
            <?php $this->widget('CommunitiesServer'); ?>
        <?php } else { ?>
            <aside>
                <?php $this->widget('CommunityData'); ?>
                <?php $this->widget('CommunityAffiliations'); ?>
            </aside>
            <div id="community">
            <?php $this->widget('CommunityHeader'); ?>
            <?php //$this->widget('Groups'); ?>
            <?php $this->widget('Group'); ?>
            <?php //$this->widget('Publish'); ?>
            </div>
        <?php } ?>
    </section>
</main>

<?php $this->widget('Search');?>
<?php $this->widget('Notification');?>
<?php $this->widget('Toast');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Upload'); ?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>
<?php $this->widget('ChatOmemo');?>
<?php $this->widget('Location');?>

<?php $this->widget('PostActions');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main style="background-color: rgb(var(--movim-background))">
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
</main>

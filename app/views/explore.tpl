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
    <div class="large">
        <?php if (!empty($_GET['s']) && $_GET['s'] == 'servers') { ?>
            <header>
                <ul class="list middle">
                    <li>
                        <span class="primary icon active" onclick="history.back()">
                            <i class="material-icons">arrow_back</i>
                        </span>
                        <div>
                            <p class="center"><?php echo __('communities.servers'); ?></p>
                            <p class="center line"><?php echo __('communities.empty_text'); ?></p>
                        </div>
                    </li>
                </ul>
            </header>
            <?php $this->widget('CommunitiesInteresting'); ?>
            <?php $this->widget('CommunitiesServers'); ?>
        <?php } else { ?>
            <header>
                <ul class="list middle">
                    <li>
                        <div>
                            <p class="center"><?php echo __('page.explore'); ?></p>
                            <p class="center line"><?php echo __('communities.empty_text'); ?></p>
                        </div>
                    </li>
                </ul>
            </header>
            <?php $this->widget('CommunitiesTags'); ?>
            <?php $this->widget('Communities'); ?>
        <?php } ?>
    </div>
</main>

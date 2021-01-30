<?php $this->widget('Search');?>
<?php $this->widget('Notification');?>
<?php $this->widget('Toast');?>
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
    <div class="large">
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
        <?php $this->widget('Tabs');?>
        <ul class="tabs" id="navtabs"></ul>
        <?php $this->widget('Communities'); ?>
        <?php $this->widget('CommunitiesServers'); ?>
    </div>
</main>

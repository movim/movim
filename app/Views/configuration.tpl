<?php $this->widget('Search');?>
<?php $this->widget('Upload');?>
<?php $this->widget('Onboarding');?>
<?php $this->widget('Notifications');?>
<?php if(me()->hasOMEMO()) $this->widget('ChatOmemo');?>

<nav>
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <div>
        <header class="on_desktop">
            <ul class="list middle">
                <li>
                    <span id="menu" class="primary icon gray" >
                        <i class="material-symbols">tune</i>
                    </span>
                    <div>
                        <p><?php echo __('page.configuration'); ?></p>
                    </div>
                </li>
            </ul>
        </header>

        <?php $this->widget('Tabs');?>
        <ul class="tabs" id="navtabs"></ul>
        <?php $this->widget('Vcard4');?>
        <?php if (me()->hasPubsub()) { ?>
            <?php $this->widget('Avatar');?>
            <?php $this->widget('Config');?>
        <?php } ?>
        <?php $this->widget('Account');?>
        <?php $this->widget('EmojisConfig');?>
        <?php $this->widget('NotificationConfig');?>
        <?php $this->widget('AdHoc', ['to' => me()->session->host]);?>
        <?php $this->widget('Blocked');?>
    </div>
</main>

<?php if (me()->hasPubsub() && me()->hasUpload()) { ?>
    <?php $this->widget('PublishStories');?>
<?php } ?>

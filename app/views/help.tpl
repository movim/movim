<?php $this->widget('Search');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('Notification');?>
<?php $this->widget('Toast');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('ChatOmemo');?>
<?php $this->widget('Location');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main>
    <div>
        <header>
            <ul class="list middle">
                <li>
                    <span id="menu" class="primary icon active gray">
                        <i class="material-icons on_desktop">help</i>
                        <i class="material-icons on_mobile" onclick="MovimTpl.toggleMenu()">menu</i>
                    </span>
                    <div>
                        <p><?php echo __('page.help'); ?></p>
                    </div>
                </li>
            </ul>
        </header>
        <?php $this->widget('Tabs');?>
        <ul class="tabs" id="navtabs"></ul>
        <?php $this->widget('Help');?>
        <?php $this->widget('About');?>
    </div>
</main>

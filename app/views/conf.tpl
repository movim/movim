<?php /* -*- mode: html -*- */
?>
<?php //$this->widget('Presence');?>
<?php //$this->widget('Chat');?>
<?php //$this->widget('VisioExt');?>

<nav class="color dark">
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <header>
        <span id="menu" class="on_mobile icon" onclick="MovimTpl.showMenu()"><i class="md md-menu"></i></span>
        <span class="on_desktop icon"><i class="md md-settings"></i></span>
        <h2>Configuration **FIXME**</h2>
    </header>
    <section>
        <div>
            <?php $this->widget('Tabs');?>
            <?php $this->widget('Config');?>
            <?php $this->widget('ConfigData');?>
            <?php $this->widget('PubsubSubscriptionConfig');?>
        </div>
    </section>
    <footer></footer>
</main>

<!--<div id="main">
    <div id="left">
        <?php //$this->widget('Profile');?>
        <?php //$this->widget('Notifs');?>
        <?php //$this->widget('Bookmark');?>
    </div>
            <?php //$this->widget('Tabs');?>
    <div id="center">
        <?php //$this->widget('Config');?>
        <?php //$this->widget('ConfigData');?>
        <?php //$this->widget('PubsubSubscriptionConfig');?>
    </div>
</div>

<div id="right">
    <?php //$this->widget('Roster');?>
</div>-->

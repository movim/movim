<?php /* -*- mode: html -*- */
?>
<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <header>
        <span id="menu" class="on_mobile icon" onclick="MovimTpl.showMenu()"><i class="md md-menu"></i></span>
        <span class="on_desktop icon"><i class="md md-help"></i></span>
        <h2>Help **FIXME**</h2>
    </header>
    <section>
        <div>
            <?php $this->widget('Tabs');?>
            <?php $this->widget('Help');?>
            <?php $this->widget('About');?>
        </div>
    </section>
    <footer></footer>
</main>

<?php //$this->widget('Presence');?>
<?php //$this->widget('Chat');?>
<?php //$this->widget('VisioExt');?>
<!--
<div id="main">
    <div id="left">
        <?php //$this->widget('Profile');?>
        <?php //$this->widget('Notifs');?>
        <?php //$this->widget('Bookmark');?>
    </div>
    <?php //$this->widget('Tabs');?>
    <div id="center">
      <?php //$this->widget('Help');?>
      <?php //$this->widget('About');?>
    </div>
</div>

<div id="right">
    <?php //$this->widget('Roster');?>
</div>
-->

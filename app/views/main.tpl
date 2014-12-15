<?php /* -*- mode: html -*- */
?>
<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <header>
        <span id="menu" class="on_mobile icon" onclick="MovimTpl.showMenu()"><i class="md md-menu"></i></span>
        <span id="back" class="on_mobile icon" onclick="MovimTpl.hidePanel()"><i class="md md-arrow-back"></i></span>
        <span class="on_desktop icon"><i class="md md-speaker-notes"></i></span>
        <h2>News **FIXME**</h2>
    </header>
    <section>
        <?php $this->widget('Menu');?>
        <?php $this->widget('Post');?>
    </section>
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

    <div id="center">
        <?php //$this->widget('Init');?>
        <?php //$this->widget('Feed');?>
    </div>

</div>

<div id="right">
    <?php 
        //$this->widget('Roster');
    ?>
</div>
-->

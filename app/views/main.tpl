<?php /* -*- mode: html -*- */
?>
<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <?php $this->widget('Header');?>
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

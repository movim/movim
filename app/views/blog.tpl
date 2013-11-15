<?php /* -*- mode: html -*- */
?>

<div id="main">
    <div id="left">
        <?php $this->widget('ContactSummary');?>
    </div>

    <?php $this->widget('Tabs');?>
    <div id="center">
        <?php $this->widget('Blog');?>
        <?php $this->widget('ContactCard');?>
    </div>
</div>

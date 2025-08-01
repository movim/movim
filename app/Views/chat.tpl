<?php $this->widget('Search');?>
<?php $this->widget('Stickers');?>
<?php $this->widget('Notifications');?>
<?php $this->widget('SendTo');?>
<?php $this->widget('ContactActions');?>
<?php $this->widget('AdHoc');?>
<?php if(me()->hasOMEMO()) $this->widget('ChatOmemo');?>

<nav>
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<?php $this->widget('BottomNavigation');?>

<main class="slide">
    <?php $this->widget('Upload');?>
    <?php $this->widget('Chat');?>
    <?php $this->widget('ChatActions');?>
    <div id="scroll_block">
        <a class="button action color" onclick="Search_ajaxRequest(true)">
            <i class="material-symbols">add</i>
        </a>
        <?php if (me()->hasPubsub()) { ?>
            <?php $this->widget('Stories');?>
        <?php } ?>
        <?php $this->widget('Chats');?>
        <?php $this->widget('Rooms');?>
        <?php $this->widget('RoomsUtils');?>
    </div>
</main>

<?php $this->widget('Dictaphone');?>
<?php $this->widget('Snap');?>
<?php if (me()->hasPubsub()) { ?>
    <?php $this->widget('PublishStories');?>
    <?php $this->widget('StoriesViewer');?>
<?php } ?>
<?php $this->widget('Tabs');?>
<?php $this->widget('Draw');?>
<?php $this->widget('RoomsExplore');?>

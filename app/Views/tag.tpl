<?php if ($this->user) { ?>
    <?php $this->widget('Search');?>
        <?php $this->widget('Notifications');?>
    <?php $this->widget('SendTo');?>
    <?php if($this->user?->hasOMEMO()) $this->widget('ChatOmemo');?>

    <?php $this->widget('PostActions');?>

    <nav>
        <?php $this->widget('Presence');?>
        <?php $this->widget('Shortcuts');?>
        <?php $this->widget('Navigation');?>
    </nav>

    <?php $this->widget('BottomNavigation');?>
<?php } ?>

<main>
    <?php if ($this->user) { ?>
        <aside>
            <?php $this->widget('NewsNav');?>
        </aside>
    <?php } ?>
    <div>
        <?php if (!$this->user) { ?>
            <?php $this->widget('PublicNavigation');?>
            <hr />
        <?php } ?>
        <?php $this->widget('Blog');?>
    </div>
</main>

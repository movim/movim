<?php if ($this->user) { ?>
    <?php $this->widget('Search');?>
    <?php $this->widget('Notifications');?>
    <?php $this->widget('SendTo');?>
    <?php if($this->user?->hasOMEMO()) $this->widget('ChatOmemo');?>
    <?php $this->widget('Visio');?>

    <?php $this->widget('PostActions');?>

    <nav aria-label="<?php echo __('global.main_menu') ?>">
        <?php $this->widget('Presence');?>
        <?php $this->widget('Shortcuts');?>
        <?php $this->widget('SpacesMenu');?>
        <?php $this->widget('Navigation');?>
    </nav>

    <?php $this->widget('BottomNavigation');?>
<?php } ?>

<main>
    <?php if ($this->user) { ?>
        <section id="sidebar">
            <?php $this->widget('NewsNav');?>
        </section>
    <?php } ?>
    <div>
        <?php if (!$this->user) { ?>
            <?php $this->widget('PublicNavigation');?>
            <hr />
        <?php } ?>
        <?php $this->widget('Blog');?>
    </div>
</main>

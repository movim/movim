<?php if (isLogged()) { ?>
    <?php $this->widget('Search');?>
        <?php $this->widget('Notifications');?>
    <?php $this->widget('SendTo');?>
    <?php if(\App\User::me()->hasOMEMO()) $this->widget('ChatOmemo');?>
    <?php $this->widget('Location');?>

    <?php $this->widget('PostActions');?>

    <nav class="color dark">
        <?php $this->widget('Presence');?>
        <?php $this->widget('Navigation');?>
    </nav>

    <?php $this->widget('BottomNavigation');?>
<?php } ?>

<main style="background-color: rgb(var(--movim-background));">
    <?php if (isLogged()) { ?>
        <aside>
            <?php $this->widget('NewsNav');?>
        </aside>
    <?php } ?>
    <div>
        <?php if (!isLogged()) { ?>
            <?php $this->widget('PublicNavigation');?>
            <hr />
        <?php } ?>
        <?php $this->widget('Blog');?>
    </div>
</main>

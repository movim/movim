<?php $this->widget('Notification');?>
<?php $this->widget('Search');?>
<?php $this->widget('VisioLink');?>
<?php $this->widget('PostActions');?>
<?php $this->widget('ContactActions');?>

<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <section style="background-color: #EEE;">
        <?php if(empty($_GET['s'])) { ?>
            <aside>
                <?php $this->widget('ContactDisco');?>
            </aside>
            <div>
                <?php $this->widget('Invitations');?>
                <?php $this->widget('Roster');?>
            </div>
        <?php } else { ?>
            <aside>
                <?php $this->widget('ContactData'); ?>
            </aside>
            <div>
                <?php $this->widget('ContactHeader'); ?>
                <?php $this->widget('CommunityPosts'); ?>
            </div>
        <?php } ?>
        <!--
        <div style="background-color: #EEE;">
            <?php //$this->widget('Invitations');?>
            <?php //$this->widget('Roster');?>
        </div>
        <div id="contact_widget" class="spin">
            <?php //$this->widget('Contact');?>
        </div>-->
    </section>
</main>

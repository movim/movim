<nav class="color dark">
    <?php $this->widget('Navigation');?>
    <?php $this->widget('Presence');?>
</nav>

<main>
    <?php $this->widget('Header'); ?>
    <section>
        <div>
            <?php $this->widget('Tabs');?>
            <?php $this->widget('Vcard4');?>
            <?php $this->widget('Avatar');?>
            <?php $this->widget('Config');?>
            <?php $this->widget('Account');?>
            <?php $this->widget('AdHoc');?>
            <?php //$this->widget('ConfigData');?>
            <?php //$this->widget('PubsubSubscriptionConfig');?>
        </div>
    </section>
</main>

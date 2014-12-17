<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <?php $this->widget('Header'); ?>
    <section>
        <div>
            <?php $this->widget('Tabs');?>
            <?php $this->widget('Vcard4');?>
            <?php $this->widget('Config');?>
            <?php $this->widget('ConfigData');?>
            <?php $this->widget('PubsubSubscriptionConfig');?>
        </div>
    </section>
</main>

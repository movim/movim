<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <header>
        <span id="menu" class="on_mobile icon" onclick="MovimTpl.showMenu()"><i class="md md-menu"></i></span>
        <span class="on_desktop icon"><i class="md md-help"></i></span>
        <h2>Help **FIXME**</h2>
    </header>
    <section>
        <div>
            <?php $this->widget('Tabs');?>
            <?php $this->widget('Help');?>
            <?php $this->widget('About');?>
        </div>
    </section>
</main>

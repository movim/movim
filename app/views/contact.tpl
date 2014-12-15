<nav class="color dark">
    <?php $this->widget('Presence');?>
    <?php $this->widget('Navigation');?>
</nav>

<main>
    <header>
        <span id="menu" class="on_mobile icon" onclick="MovimTpl.showMenu()"><i class="md md-menu"></i></span>
        <span id="back" class="on_mobile icon" onclick="MovimTpl.hidePanel()"><i class="md md-arrow-back"></i></span>
        <span class="on_desktop icon"><i class="md md-people"></i></span>
        <h2>Contacts **FIXME**</h2>
    </header>
    <section>
        <?php $this->widget('Roster');?>
        <div>
            <?php $this->widget('Contact');?>
        </div>
    </section>
</main>

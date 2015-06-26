<div>
    <span id="menu" class="on_mobile icon active" onclick="MovimTpl.toggleMenu()"><i class="zmdi zmdi-menu"></i></span>
    <span class="on_desktop icon"><i class="zmdi zmdi-search"></i></span>

    <form>
        <div onclick="Roster.init();">
            <input type="text" name="search" id="rostersearch" autocomplete="off" placeholder="{$c->__('roster.search');}"/>
        </div>
    </form>
</div>

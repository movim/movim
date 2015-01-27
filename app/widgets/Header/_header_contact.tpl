<div>
    <ul class="active">
        <li onclick="Roster_ajaxDisplaySearch()">
            <span class="icon">
                <i class="md md-person-add"></i>
            </span>
        </li>
    </ul>
    <span id="menu" class="on_mobile icon active" onclick="MovimTpl.toggleMenu()"><i class="md md-menu"></i></span>
    <span class="on_desktop icon"><i class="md md-search"></i></span>

    <form>
        <div onclick="Roster.init();">
            <input type="text" name="search" id="rostersearch" autocomplete="off" placeholder="{$c->__('roster.search');}"/>
        </div>
    </form>
</div>

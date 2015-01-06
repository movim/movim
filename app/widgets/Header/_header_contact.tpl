<ul class="active">
    <li onclick="Roster_ajaxDisplaySearch()">
        <span class="icon">
            <i class="md md-person-add"></i>
        </span>
    </li>
</ul>
<span id="menu" class="on_mobile icon" onclick="MovimTpl.showMenu()"><i class="md md-menu"></i></span>
<span class="on_desktop icon"><i class="md md-search"></i></span>
<!--<h2>{$c->__('page.contacts')}</h2>-->

<form>
    <div onclick="Roster.init();">
        <input type="text" name="search" id="rostersearch" autocomplete="off" placeholder="{$c->__('roster.search');}"/>
        <!--<label for="search">{$c->__('roster.search')}</label>-->
    </div>
</form>

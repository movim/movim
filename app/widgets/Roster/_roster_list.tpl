<header>
    <ul class="list">
        <li>
            <span id="menu" class="primary on_mobile icon active gray" onclick="MovimTpl.toggleMenu()"><i class="zmdi zmdi-menu"></i></span>
            <span class="primary on_desktop icon gray"><i class="zmdi zmdi-search"></i></span>
            <form>
                <div onclick="Roster.init();">
                    <input type="text" name="search" id="rostersearch" autocomplete="off" placeholder="{$c->__('roster.search');}"/>
                </div>
            </form>
        </li>
    </ul>
</header>
<ul id="rosterlist" class="list active thin">
    {loop="$contacts"}
        {$c->prepareItem($value)}
    {/loop}
</ul>
<a onclick="Roster_ajaxDisplaySearch()" class="button action color" title="{$c->__('roster.search')}">
    <i class="zmdi zmdi-account-add"></i>
</a>

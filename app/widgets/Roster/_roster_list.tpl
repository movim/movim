<header>
    <ul class="list">
        <li>
            {if="count($contacts) > 5"}
            <span class="primary icon bubble gray active">
                <i class="zmdi zmdi-menu on_mobile" onclick="MovimTpl.toggleMenu()"></i>
                <i class="zmdi zmdi-search on_desktop"></i>
            </span>
            <form onsubmit="return false;">
                <div onclick="Roster.init();">
                    <input type="text" name="search" id="rostersearch" autocomplete="off" placeholder="{$c->__('roster.search');}"/>
                </div>
            </form>
            {else}
                <span class="primary on_desktop icon gray bubble">
                    <i class="zmdi zmdi-accounts on_desktop"></i>
                    <i class="zmdi zmdi-menu on_mobile" onclick="MovimTpl.toggleMenu()"></i>
                </span>
                <span class="control icon active gray on_mobile" onclick="MovimTpl.showPanel()">
                    <i class="zmdi zmdi-eye"></i>
                </span>
                <p class="line">{$c->__('page.contacts')}</p>
            {/if}
        </li>
    </ul>
</header>
<ul id="rosterlist" class="list active thin flex">
    {loop="$contacts"}
        {$c->prepareItem($value)}
    {/loop}

    {if="empty($contacts)"}
        <div ng-if="contacts == null" class="empty placeholder icon contacts">
            <h1>{$c->__('roster.no_contacts_title')}</h1>
            <h4>{$c->__('roster.no_contacts_text')}</h4>
            <a class="button flat on_mobile"
               onclick="MovimTpl.showPanel()">
               <i class="zmdi zmdi-eye"></i>  {$c->__('button.discover')}
            </a>
        </div>
    {/if}
</ul>
<a onclick="Roster_ajaxDisplaySearch()" class="button action color" title="{$c->__('roster.add_title')}">
    <i class="zmdi zmdi-account-add"></i>
</a>

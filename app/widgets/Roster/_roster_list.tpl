<header>
    <ul class="list">
        <li>
                {if="count($contacts) > 5"}
                <span class="primary icon bubble gray">
                    <i class="material-icons">search</i>
                </span>
                <form onsubmit="return false;">
                    <div onclick="Roster.init();">
                        <input type="text" name="search" id="rostersearch" autocomplete="off" placeholder="{$c->__('roster.search');}"/>
                    </div>
                </form>
            {/if}
        </li>
    </ul>
</header>
<ul id="rosterlist" class="list active flex">
    {loop="$contacts"}
        {$c->prepareItem($value)}
    {/loop}

    {if="$contacts->isEmpty()"}
        <div class="placeholder empty">
            <i class="material-icons">people</i>
            <h1>{$c->__('roster.no_contacts_title')}</h1>
            <h4>{$c->__('roster.no_contacts_text')}</h4>
        </div>
    {/if}
</ul>
<br />
<!--<a onclick="Roster_ajaxDisplaySearch()" class="button action color" title="{$c->__('roster.add_title')}">
    <i class="material-icons">account-add</i>
</a>-->

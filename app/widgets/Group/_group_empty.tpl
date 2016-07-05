<header>
    <ul class="list middle">
        <li>
            <span class="primary icon gray active on_mobile" onclick="MovimTpl.hidePanel()">
                <i class="zmdi zmdi-arrow-left"></i>
            </span>
            <p class="center">{$c->__('group.empty_title')}</p>
            <p class="center line">{$c->__('group.empty_text')}</p>
        </li>
    </ul>
</header>

<ul class="list card thick">
    <li></li>
    <li class="block">
        <span class="primary icon gray">
            <i class="zmdi zmdi-help"></i>
        </span>
        <p class="all">
            {$c->__('group.help_info1')}
        </p>
        <p>
            {$c->___('group.help_info2', '<i class="zmdi zmdi-bookmark"></i>', '<i class="zmdi zmdi-plus"></i> ')}<br />
            {$c->___('group.help_info3', '<i class="zmdi zmdi-edit"></i>')}<br />
        </p>
        <p>
            {$c->___('group.help_info4', '<a href="'.$c->route('news').'"><i class="zmdi zmdi-receipt"></i> ','</a>')}
        </p>
    </li>
</ul>
<ul class="list flex middle active">
    <li class="block large">
        <p>{$c->__('group.servers')}</p>
    </li>
    {loop="$servers"}
        {if="!filter_var($value->server, FILTER_VALIDATE_EMAIL)"}
            <li class="block" onclick="Groups_ajaxDisco('{$value->server}'); MovimTpl.hidePanel();">
                <span class="primary icon bubble color {$value->server|stringToColor}">
                    {$value->server|firstLetterCapitalize}
                </span>
                <p class="line" title="{$value->server} - {$value->name}">
                    {$value->server}
                    <span class="second">{$value->name}</span>
                </p>
                <p>{$c->__('group.counter', $value->number)}</p>
            </li>
        {/if}
    {/loop}
    <li class="block large">
        <span class="primary icon">
            <i class="zmdi zmdi-search-for"></i>
        </span>
        <form>
            <div>
                <input placeholder="pubsub.server.com" onkeypress="
                    if(event.keyCode == 13) { Groups_ajaxDisco(this.value); return false; }" >
                <label>{$c->__('group.search_server')}</label>
            </div>
        </form>
    </li>
</ul>

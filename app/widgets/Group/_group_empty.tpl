<div class="placeholder icon pages">
    <h1>{$c->__('group.empty_title')}</h1>
    <h4>{$c->__('group.empty_text')}</h4>
</div>
<ul class="card thick">
    <li class="block">
        <span class="icon gray">
            <i class="zmdi zmdi-help"></i>
        </span>
        <p class="all">
            {$c->__('group.help_info1')}<br />
            <br />
            {$c->___('group.help_info2', '<i class="zmdi zmdi-bookmark"></i>', '<i class="zmdi zmdi-plus"></i> ')}<br />
            {$c->___('group.help_info3', '<i class="zmdi zmdi-edit"></i>')}<br />
            <br />
            {$c->___('group.help_info4', '<a href="'.$c->route('news').'">','</a>')}
        </p>
    </li>
</ul>
<h2>{$c->__('group.servers')}</h2>
<ul class="flex middle active">
    {loop="$servers"}
        {if="!filter_var($value->server, FILTER_VALIDATE_EMAIL)"}
            <li class="block condensed" onclick="Groups_ajaxDisco('{$value->server}')">
                <span class="icon bubble color {$value->server|stringToColor}">{$value->server|firstLetterCapitalize}</span>
                <span title="{$value->server} - {$value->name}">{$value->server} - {$value->name}</span>
                <p>{$c->__('group.counter', $value->number)}</p>
            </li>
        {/if}
    {/loop}
    <li class="block large">
        <span class="icon">
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

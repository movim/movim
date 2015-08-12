<div class="placeholder icon pages">
    <h1>{$c->__('group.empty_title')}</h1>
    <h4>{$c->__('group.empty_text')}</h4>
</div>
<br />
<h2>{$c->__('group.servers')}</h2>
<ul class="flex card shadow active">
    {loop="$servers"}
        {if="!filter_var($value->server, FILTER_VALIDATE_EMAIL)"}
            <li class="block condensed" onclick="Groups_ajaxDisco('{$value->server}')">
                <span class="icon bubble color {$value->server|stringToColor}">{$value->server|firstLetterCapitalize}</span>
                <span>{$value->server}</span>
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

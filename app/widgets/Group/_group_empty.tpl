<div class="placeholder icon pages">
    <h1>{$c->__('group.empty_title')}</h1>
    <h4>{$c->__('group.empty_text')}</h4>
</div>
<br />
<ul class="flex middle active">
    {loop="$servers"}
        {if="!filter_var($value->server, FILTER_VALIDATE_EMAIL)"}
            <li class="block condensed" onclick="Groups_ajaxDisco('{$value->server}')">
                <span class="icon bubble color {$value->server|stringToColor}">{$value->server|firstLetterCapitalize}</span>
                <span>{$value->server}</span>
                <p>{$c->__('group.counter', $value->number)}</p>
            </li>
        {/if}
    {/loop}
</ul>

{loop="$shortcuts"}
    {if="get_class($value) == 'App\Roster' && $counter = $c->me->unreads($value->jid)"}
        <li onclick="Shortcuts.clear('{$value->jid|echapJS}'); Search.chat('{$value->jid|echapJS}', false)" data-jid="{$value->jid|echapJS}">
            <span class="primary icon bubble" data-counter="{$counter}">
                <img src="{$value->getPicture()}">
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chat_paste_go</i>
            </span>
            <div>
                <p class="line">{$value->truename}</p>
            </div>
        </li>
    {elseif="get_class($value) == 'App\Contact' && $counter = $c->me->unreads($value->id)"}
        <li onclick="Shortcuts.clear('{$value->id|echapJS}'); Search.chat('{$value->id|echapJS}', false)" data-jid="{$value->id|echapJS}">
            <span class="primary icon bubble" data-counter="{$counter}">
                <img src="{$value->getPicture()}">
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chat_paste_go</i>
            </span>
            <div>
                <p class="line">{$value->truename}</p>
            </div>
        </li>
    {elseif="get_class($value) == 'App\Conference'"}
        <li onclick="Shortcuts.clear('{$value->conference|echapJS}'); Search.chat('{$value->conference|echapJS}', true)" data-jid="{$value->conference|echapJS}">
            <span class="primary icon bubble symbol" data-counter="notifications">
                <img src="{$value->getPicture()}">
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chat_paste_go</i>
            </span>
            <div>
                <p class="line">{$value->title}</p>
            </div>
        </li>
    {/if}
{/loop}
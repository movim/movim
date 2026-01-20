{loop="$shortcuts"}
    {if="get_class($value) == 'App\Roster'"}
        <li onclick="Search.chat('{$value->jid|echapJS}', false)" data-jid="{$value->jid|echapJS}">
            <span class="primary icon bubble" data-counter="{$c->me->unreads($value->jid)}">
                <img src="{$value->getPicture()}">
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chat_paste_go</i>
            </span>
            <div>
                <p class="line">{$value->truename}</p>
            </div>
        </li>
    {elseif="get_class($value) == 'App\Contact'"}
        <li onclick="Search.chat('{$value->id|echapJS}', false)" data-jid="{$value->id|echapJS}">
            <span class="primary icon bubble" data-counter="{$c->me->unreads($value->id)}">
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
        <li onclick="Search.chat('{$value->conference|echapJS}', true)" data-jid="{$value->conference|echapJS}">
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
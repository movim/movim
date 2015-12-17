<div class="placeholder icon">
    <h1>{$c->__('chat.empty_title')}</h1>
    <h4>{$c->__('chat.empty_text')}</h4>
</div>

<ul class="list flex middle active">
    {if="$top"}
        <li class="subheader block large">
            <p>{$c->__('chat.frequent')}</p>
        </li>
    {/if}
    {loop="$top"}
        <li class="block {if="$value->last > 60"} inactive{/if}"
            onclick="Chats_ajaxOpen('{$value->jid}'); Chat_ajaxGet('{$value->jid}');">
            {$url = $value->getPhoto('s')}
            {if="$url"}
                <span class="primary icon bubble
                    {if="$value->value"}
                        status {$presencestxt[$value->value]}
                    {/if}">
                    <img src="{$url}">
                </span>
            {else}
                <span class="primary icon bubble color {$value->jid|stringToColor}
                    {if="$value->value"}
                        status {$presencestxt[$value->value]}
                    {/if}">
                    <i class="zmdi zmdi-account"></i>
                </span>
            {/if}
            <p>{$value->getTrueName()}</p>
            <p>{$value->jid}</p>
        </li>
    {/loop}
</ul>
<br />
<br />

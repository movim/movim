<div class="placeholder icon">
    <h1>{$c->__('chat.empty_title')}</h1>
    <h4>{$c->__('chat.empty_text')}</h4>
</div>

<ul class="flex middle active">
    {if="$top"}
        <li class="subheader block large">{$c->__('chat.frequent')}</li>
    {/if}
    {loop="$top"}
        <li class="condensed block {if="$value->last > 60"} inactive{/if}"
            onclick="Chats_ajaxOpen('{$value->jid}'); Chat_ajaxGet('{$value->jid}');">
            {$url = $value->getPhoto('s')}
            {if="$url"}
                <span class="icon bubble
                    {if="$value->value"}
                        status {$presencestxt[$value->value]}
                    {/if}">
                    <img src="{$url}">
                </span>
            {else}
                <span class="icon bubble color {$value->jid|stringToColor}
                    {if="$value->value"}
                        status {$presencestxt[$value->value]}
                    {/if}">
                    <i class="zmdi zmdi-account"></i>
                </span>
            {/if}
            <span>{$value->getTrueName()}</span>
            <p class="wrap">{$value->jid}</p>
        </li>
    {/loop}
</ul>
<br />
<br />

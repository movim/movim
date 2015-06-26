{if="$chats == null"}
    <div class="placeholder icon">
        <h1>{$c->__('chats.empty_title')}</h1>
        <h4>{$c->__('chats.empty')}</h4>
    </div>
{/if}

{loop="$chats"}
    {$c->prepareChat($key)}
{/loop}

<a onclick="Chats_ajaxAdd()" class="button action color">
    <i class="zmdi zmdi-plus"></i>
</a>

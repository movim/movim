{loop="$chats"}
    <li data-jid="{$value->jid}">
        <div class="control">
            <i class="md md-chevron-right"></i>
        </div>
        <span class="icon bubble">
            <img src="{$value->getPhoto('s')}">
        </span>
        <span>{$value->getTrueName()}</span>
    </li>
{/loop}

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
<li class="subheader">
    Chatrooms **FIXME**
</li>
{loop="$conferences"}
    <li  data-jid="{$value->conference}">
        <span class="icon bubble color {$value->name|stringToColor}">{$value->name|firstLetterCapitalize}</span>
        <span>{$value->name}</span>
    </li>
{/loop}

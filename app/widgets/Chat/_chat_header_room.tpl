<div>
    <ul class="active">
        <li onclick="Rooms_ajaxAdd()">
            <span class="icon">
                <i class="md md-group-add"></i>
            </span>
        </li>
    </ul>
    <span class="on_desktop icon"><i class="md md-forum"></i></span>
    <h2>{$c->__('page.chats')}</h2>
</div>
<div>
    <ul class="active">
        <li onclick="Rooms_ajaxExit('{$room}'); MovimTpl.hidePanel();">
            <span class="icon">
                <i class="md md-close"></i>
            </span>
        </li>
        <li class="thin show_context_menu">
            <span class="icon">
                <i class="md md-more-vert"></i>
            </span>
        </li>
    </ul>
    <div class="return active r2 {if="$subject != null"}condensed{/if}" onclick="MovimTpl.hidePanel(); Chat_ajaxGet();">
        <span id="back" class="icon" ><i class="md md-arrow-back"></i></span>
        {if="$conference != null && $conference->name"}
            <h2 title="{$room}">{$conference->name}</h2>
        {else}
            <h2>{$room}</h2>
        {/if}
        {if="$subject != null"}
            <h4 title="{$subject->subject}">{$subject->subject}</h4>
        {/if}
    </div>
    <ul class="simple context_menu active">
        <li onclick="Rooms_ajaxList('{$room}')">
            <span>{$c->__('chatroom.members')}</span>
        </li>
        <li onclick="Rooms_ajaxRemoveConfirm('{$room}')">
            <span>{$c->__('button.delete')}</span>
        </li>
        {if="$presence != null && $presence->mucrole == 'moderator'"}
            <li onclick="Chat_ajaxGetRoomConfig('{$room}')">
                <span>{$c->__('chatroom.config')}</span>
            </li>
            <li onclick="Chat_ajaxGetSubject('{$room}')">
                <span>{$c->__('chatroom.subject')}</span>
            </li>
        {/if}
    </ul>
</div>

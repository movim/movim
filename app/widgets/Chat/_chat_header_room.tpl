{$anon = $c->supported('anonymous')}
<div>
    {if="!$anon"}
        <ul class="active">
            <li onclick="Rooms_ajaxAdd()">
                <span class="icon">
                    <i class="zmdi zmdi-group-add"></i>
                </span>
            </li>
        </ul>
    {/if}
    <span class="on_desktop icon"><i class="zmdi zmdi-comments"></i></span>
    {if="!$anon"}
        <h2>{$c->__('page.chats')}</h2>
    {else}
        <h2>{$c->__('page.room')}</h2>
    {/if}
</div>
<div>
    <ul class="active">
        <li onclick="Rooms_ajaxExit('{$room}'); MovimTpl.hidePanel(); {if="$anon"}Presence_ajaxLogout(){/if}">
            <span class="icon">
                <i class="zmdi zmdi-close"></i>
            </span>
        </li>
        <li class="thin show_context_menu">
            <span class="icon">
                <i class="zmdi zmdi-more-vert"></i>
            </span>
        </li>
    </ul>
    <div
        class="return {if="!$anon"}active{/if} r2 {if="$subject != null"}condensed{/if}"
        {if="!$anon"}onclick="MovimTpl.hidePanel(); Chat_ajaxGet();"{/if}>
        <span id="back" class="icon" >
            {if="!$anon"}
                <i class="zmdi zmdi-arrow-back"></i>
            {else}
                <i class="zmdi zmdi-comment-text-alt"></i>
            {/if}
        </span>

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
        {if="!$anon"}
            <li onclick="Rooms_ajaxRemoveConfirm('{$room}')">
                <span>{$c->__('button.delete')}</span>
            </li>
        {/if}
        {if="$presence != null && $presence->mucrole == 'moderator' && !$anon"}
            <li onclick="Chat_ajaxGetRoomConfig('{$room}')">
                <span>{$c->__('chatroom.config')}</span>
            </li>
            <li onclick="Chat_ajaxGetSubject('{$room}')">
                <span>{$c->__('chatroom.subject')}</span>
            </li>
        {/if}
    </ul>
</div>

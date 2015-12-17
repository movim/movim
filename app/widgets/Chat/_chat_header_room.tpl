{$anon = $c->supported('anonymous')}
<div>
    {if="!$anon"}
        <!--<ul class="active">
            <li onclick="Rooms_ajaxAdd()">
                <span class="icon">
                    <i class="zmdi zmdi-group-add"></i>
                </span>
            </li>
        </ul>-->
    {/if}
    <ul class="list middle">
        <li>
            <span class="primary on_desktop icon"><i class="zmdi zmdi-comments"></i></span>
            {if="!$anon"}
                <p>{$c->__('page.chats')}</p>
            {else}
                <p>{$c->__('page.room')}</p>
            {/if}
        </li>
    </ul>
</div>
<div>
    <ul class="list middle active">
        <li>
            <span id="back" class="primary icon active" {if="!$anon"}onclick="Header_ajaxReset('chat'); MovimTpl.hidePanel(); Chat_ajaxGet();"{/if}>
                {if="!$anon"}
                    <i class="zmdi zmdi-arrow-back"></i>
                {else}
                    <i class="zmdi zmdi-comment-text-alt"></i>
                {/if}
            </span>

            <span class="control icon show_context_menu active">
                <i class="zmdi zmdi-more-vert"></i>
            </span>

            {if="$c->supported('upload')"}
                <span class="control icon active" onclick="Upload_ajaxRequest()">
                    <i class="zmdi zmdi-attachment-alt"></i>
                </span>
            {/if}
            <span class="control icon active" onclick="Rooms_ajaxExit('{$room}'); MovimTpl.hidePanel(); {if="$anon"}Presence_ajaxLogout(){/if}">
                <i class="zmdi zmdi-close"></i>
            </span>

            {if="$conference != null && $conference->name"}
                <p class="line" title="{$room}">{$conference->name}</p>
            {else}
                <p class="line">{$room}</p>
            {/if}
            {if="$subject != null"}
                <p class="line" title="{$subject->subject}">{$subject->subject}</p>
            {/if}
        </li>
    </ul>

    <ul class="list context_menu active">
        <li onclick="Rooms_ajaxList('{$room}')">
            <p class="normal">{$c->__('chatroom.members')}</p>
        </li>
        {if="!$anon"}
            <li onclick="Rooms_ajaxRemoveConfirm('{$room}')">
                <p class="normal">{$c->__('button.delete')}</p>
            </li>
        {/if}
        {if="$presence != null && $presence->mucrole == 'moderator' && !$anon"}
            <li onclick="Chat_ajaxGetRoomConfig('{$room}')">
                <p class="normal">{$c->__('chatroom.config')}</p>
            </li>
            <li onclick="Chat_ajaxGetSubject('{$room}')">
                <p class="normal">{$c->__('chatroom.subject')}</p>
            </li>
        {/if}
    </ul>
</div>

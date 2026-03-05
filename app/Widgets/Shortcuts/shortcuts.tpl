<ul class="list navigation active" id="shortcuts_widget" dir="ltr"></ul>
<ul class="navigation list active" dir="ltr">
    <li onclick="{if="$page == 'chat'"}Rooms.toggleScroll(){else}MovimUtils.reload('{$c->route('chat')}'){/if}"
        class="{if="$page == 'chat'"}active{/if}"
        title="{$c->__('page.chats')}"
    >
        <span class="primary icon" id="chatcounter" {if="$chatCounter > 0"}data-counter="{$chatCounter}"{/if}>
            <i class="material-symbols">chat_bubble</i>
        </span>
        <div>
            <p>{$c->__('page.chats')}</p>
        </div>
    </li>
</ul>

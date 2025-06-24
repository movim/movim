<ul id="chats_calls_list" class="list middle active divided spaced">
    {autoescape="off"}
        {$c->prepareCalls()}
    {/autoescape}
</ul>
<ul id="chats_widget_header" class="list" data-filter="{$filter}">
    <li class="subheader">
        <div>
            <p class="normal">
                {$c->__('page.chats')}
            </p>
        </div>
        {loop="$filters"}
            <span class="chip active" data-filter="{$value}" onclick="Chats.setFilter(this.dataset.filter)">{$c->__('chats_filter.' . $value)}</span>
        {/loop}
    </li>
</ul>
<ul id="chats" class="list middle active divided spaced">
    {autoescape="off"}
        {$c->prepareChats()}
    {/autoescape}
</ul>

<div class="placeholder">
    <i class="material-symbols fill">chat</i>
    <h1>{$c->__('chats.empty_title')}</h1>
    <h4>{$c->___('chats.empty', '<i class="material-symbols">add</i>')}</h4>
</div>

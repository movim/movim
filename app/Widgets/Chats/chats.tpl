<ul id="chats_widget_header" class="list" data-filter="{$filter}">
    <li class="subheader">
        <div>
            <p>
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
    <i class="material-symbols fill">chat_dashed</i>
    <h1>{$c->__('chats.empty_title')}</h1>
    <h4>{autoescape="off"}{$addplaceholder}{/autoescape}</h4>
</div>

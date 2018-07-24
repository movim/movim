<ul class="list">
    <li class="subheader">
        <p class="normal">
            {$c->__('page.chats')}
        </p>
    </li>
</ul>
<ul id="chats_widget_list" class="list middle active divided spaced">
    {$c->prepareChats(true)}
</ul>

<div class="placeholder">
    <i class="material-icons">chat</i>
    <h1>{$c->__('chats.empty_title')}</h1>
    <h4>{$c->___('chats.empty', '<i class="material-icons">add</i>', '<a href="'.$c->route('contact').'"><i class="material-icons">people</i> ', '</a>')}</h4>
</div>

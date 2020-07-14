<ul class="list">
    <li class="subheader">
        <div>
            <p class="normal">
                {$c->__('page.chats')}
            </p>
        </div>
        <span class="control icon active gray" onclick="Search_ajaxRequest()">
            <i class="material-icons">person_search</i>
        </span>
    </li>
</ul>
<ul id="chats_widget_list" class="list middle active divided spaced">
    {autoescape="off"}
        {$c->prepareChats(true)}
    {/autoescape}
</ul>

<div class="placeholder">
    <i class="material-icons">chat</i>
    <h1>{$c->__('chats.empty_title')}</h1>
    <h4>{$c->___('chats.empty', '<i class="material-icons">search</i>')}</h4>
</div>

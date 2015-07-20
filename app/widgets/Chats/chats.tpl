<ul id="chats_widget_list" class="middle active divided spaced">{$list}</ul>

<div class="placeholder icon">
    <h1>{$c->__('chats.empty_title')}</h1>
    <h4>{$c->__('chats.empty')}</h4>
</div>

<a onclick="Chats_ajaxAdd()" class="button action color">
    <i class="zmdi zmdi-plus"></i>
</a>

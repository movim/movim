<ul id="chats_widget_list" class="middle active divided spaced">{$list}</ul>

<div class="placeholder icon">
    <h1>{$c->__('chats.empty_title')}</h1>
    <h4>{$c->___('chats.empty', '<i class="zmdi zmdi-plus"></i>', '<a href="'.$c->route('contact').'"><i class="zmdi zmdi-accounts"></i> ', '</a>')}</h4>
</div>
<a class="button action color" onclick="MovimTpl.toggleActionButton()">
    <i class="zmdi zmdi-plus"></i>

    <ul class="actions">
        <li onclick="Chats_ajaxAdd()">
            <i class="zmdi zmdi-account-add"></i>
        </li>
        <li onclick="Rooms_ajaxAdd()">
            <i class="zmdi zmdi-accounts-add"></i>
        </li>
    </ul>
</a>

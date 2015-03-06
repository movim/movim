<div>
    <span class="on_desktop icon"><i class="md md-forum"></i></span>
    <h2>{$c->__('page.chats')}</h2>
</div>
<div>
    <ul class="active">
        <li onclick="Rooms_ajaxList('{$room}')">
            <span class="icon">
                <i class="md md-menu"></i>
            </span>
        </li>
        <li onclick="Rooms_ajaxRemoveConfirm('{$room}')">
            <span class="icon">
                <i class="md md-delete"></i>
            </span>
        </li>
        <li onclick="Rooms_ajaxExit('{$room}'); MovimTpl.hidePanel();">
            <span class="icon">
                <i class="md md-close"></i>
            </span>
        </li>
    </ul>
    <h2 class="active r3" onclick="MovimTpl.hidePanel(); Chat_ajaxGet();">
        <span id="back" class="icon" ><i class="md md-arrow-back"></i></span>
        {$room}
    </h2>
</div>

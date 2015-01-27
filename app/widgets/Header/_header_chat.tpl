<div>
    <ul class="active">
        <li onclick="Chats_ajaxAdd()">
            <span class="icon">
                <i class="md md-person-add"></i>
            </span>
        </li>
        <li onclick="Rooms_ajaxAdd()">
            <span class="icon">
                <i class="md md-group-add"></i>
            </span>
        </li>
    </ul>
    <span id="menu" class="on_mobile icon active" onclick="MovimTpl.toggleMenu()"><i class="md md-menu"></i></span>
    <span class="on_desktop icon"><i class="md md-forum"></i></span>
    <h2 class="r2">{$c->__('page.chats')}</h2>
</div>

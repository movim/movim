<div id="rooms">
    <ul class="list rooms divided thin spaced active spin"></ul>

    <ul class="list head flex active">
        <li class="subheader" title="{$c->__('page.configuration')}">
            <div>
                <p>
                    {$c->__('chatrooms.title')}
                </p>
            </div>

            <span class="chip active" data-filter="all" onclick="Rooms.toggleShowAll()">{$c->__('rooms_filter.all')}</span>
            <span class="chip active" data-filter="connected" onclick="Rooms.toggleShowAll()">{$c->__('rooms_filter.connected')}</span>
            <span class="control icon active gray" onclick="Rooms.toggleEdit()">
                <i class="material-symbols">rule</i>
            </span>
        </li>
        <li class="join divided" onclick="RoomsExplore_ajaxSearch();">
            <span class="primary icon gray">
                <i class="material-symbols">groups</i>
            </span>
            <div>
                <p class="line">
                    {$c->__('button.join')}
                </p>
            </div>
        </li>
        <li class="add divided" onclick="RoomsUtils_ajaxAdd(false, null, true); Drawer.clear()">
            <span class="primary icon transparent gray">
                <i class="material-symbols">group_add</i>
            </span>
            <div>
                <p class="line">
                    {$c->__('button.create')}
                </p>
            </div>
        </li>
    </ul>

    <ul class="list thick active spaced toggle_show">
        <li onclick="Rooms.toggleShowAll()">
            <span class="primary icon gray small">
                <i class="material-symbols">
                    expand_less
                </i>
                <i class="material-symbols">
                    expand_more
                </i>
            </span>
            <div>
                <p class="line">
                    {$c->__('rooms.hide_disconnected')}
                </p>
                <p class="line">
                    {$c->__('rooms.show_all')}
                </p>
            </div>
        </li>
    </ul>
</div>

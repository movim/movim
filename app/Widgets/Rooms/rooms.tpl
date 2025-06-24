<div id="rooms">
    <ul class="list rooms divided spaced thin active spin"></ul>

    <ul class="list head thin">
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
                <p class="normal line center">
                    {$c->__('rooms.hide_disconnected')}
                </p>
                <p class="normal line center">
                    {$c->__('rooms.show_all')}
                </p>
            </div>
        </li>
    </ul>
</div>

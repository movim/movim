<div id="rooms_widget">
    <ul class="list thin">
        <li class="subheader block large" title="{$c->__('page.configuration')}">
            <div>
                <p>
                    {$c->__('chatrooms.title')}
                </p>
            </div>
            <span class="control icon active gray" onclick="Rooms.toggleEdit()">
                <i class="material-icons">rule</i>
            </span>
        </li>
    </ul>

    <ul class="list rooms divided spaced thin active spin"></ul>

    <ul class="list thick active spaced toggle_show">
        <li onclick="Rooms.toggleShowAll()">
            <span class="primary icon gray small">
                <i class="material-icons">
                    expand_less
                </i>
                <i class="material-icons">
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

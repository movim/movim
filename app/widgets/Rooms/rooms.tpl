<div id="rooms_widget">
    <ul class="list divided spaced thin">
        <li class="subheader" title="{$c->__('page.configuration')}">
            <div>
                <p>
                    {$c->__('chatrooms.title')}
                </p>
            </div>
            <span class="control icon active gray" onclick="Rooms.toggleEdit()">
                <i class="material-icons flip-hor">view_list</i>
            </span>
        </li>
        <li class="divided spaced active">
            <span class="primary small icon small gray">
                <i class="material-icons">explore</i>
            </span>
            <span class="control icon gray active divided" onclick="RoomsUtils_ajaxAdd();">
                <i class="material-icons">group_add</i>
            </span>
            <div>
                <p class="normal line" onclick="RoomsExplore_ajaxSearch();">{$c->__('rooms.add')}</p>
            </div>
        </li>
    </ul>
    <ul class="list rooms divided spaced thin active"></ul>

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

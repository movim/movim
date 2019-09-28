{if="$edit"}
    <a class="button action color green" onclick="Rooms_ajaxDisplay(false, {if="$all"}true{else}false{/if})">
        <i class="material-icons">check</i>
    </a>
{/if}

{$previousConnected = true}

{if="!$c->supported('anonymous') && $c->getView() != 'room'"}
    <ul class="list divided spaced middle {if="!$edit"}active{/if}">
        <li class="subheader" title="{$c->__('page.configuration')}">
            {if="$conferences->isNotEmpty() && !$edit"}
            <span class="control icon active gray" onclick="Rooms_ajaxDisplay(true, {if="$all"}true{else}false{/if});">
                <i class="material-icons">edit</i>
            </span>
            <span class="control icon active gray" onclick="Rooms_ajaxAdd()">
                <i class="material-icons">add</i>
            </span>
            {/if}
            <p>
                <span class="info">{$conferences|count}</span>
                {$c->__('chatrooms.title')}
            </p>
        </li>
        {loop="$conferences"}
            {$connected = $value->presence}
            {if="!$connected && $previousConnected"}
                </ul>
                <ul class="list divided thin spaced {if="!$edit"}active{/if}">
            {/if}
            {$previousConnected = $connected}
            {if="$value->connected || $all"}
                <li {if="!$edit"} data-jid="{$value->conference}" {/if}
                    {if="$value->nick != null"} data-nick="{$value->nick}" {/if}
                    class="room {if="$connected"}online{/if}"
                    title="{$value->conference}">
                    {$url = $value->getPhoto()}
                    {if="$url"}
                        <span class="primary
                            {if="!$connected"}disabled small{/if} icon bubble color
                            {$value->name|stringToColor}"
                            id="{$value->conference|cleanupId}-rooms-primary"
                            style="background-image: url({$url});">
                            {autoescape="off"}
                                {$c->prepareRoomCounter($value, true)}
                            {/autoescape}
                        </span>
                    {else}
                        <span class="primary
                            {if="!$connected"}disabled small{/if} icon bubble color
                            {$value->name|stringToColor}"
                            id="{$value->conference|cleanupId}-rooms-primary">
                            {autoescape="off"}
                                {$c->prepareRoomCounter($value, false)}
                            {/autoescape}
                        </span>
                    {/if}

                    {$info = $value->info}
                    {if="$edit"}
                        <span class="control icon active gray" onclick="Rooms_ajaxRemoveConfirm('{$value->conference}');">
                            <i class="material-icons">delete</i>
                        </span>
                        <span class="control icon active gray" onclick="Rooms_ajaxAdd('{$value->conference}');">
                            <i class="material-icons">edit</i>
                        </span>
                    {/if}

                    <p class="normal line">
                        {$value->name}
                        {if="$connected"}
                            <span class="second">{$value->conference}</span>
                        {else}
                            –
                        {/if}
                    {if="$connected"}
                    </p>
                    <p class="line"
                        {if="isset($info) && $info->description"}title="{$info->description}"{/if}>
                    {/if}
                        {if="$connected"}
                            {$count = $value->presences()->count()}
                            <span title="{$c->__('communitydata.sub', $count)}"
                                {if="$connected && $connected->mucrole == 'moderator'"}
                                    class="moderator"
                                {/if}>
                                {$count} <i class="material-icons">people</i>
                                {if="$value->info && !$value->info->mucsemianonymous"}
                                    <i class="material-icons">wifi_tethering</i>
                                {/if}
                            </span> –
                        {elseif="isset($info) && $info->occupants > 0"}
                            <span title="{$c->__('communitydata.sub', $info->occupants)}"
                                {if="$connected && $connected->mucrole == 'moderator'"}
                                    class="moderator"
                                {/if}>
                                {$info->occupants} <i class="material-icons">people</i>
                                {if="$value->info && !$value->info->mucsemianonymous"}
                                    <i class="material-icons">wifi_tethering</i>
                                {/if}
                            </span> –
                        {/if}
                        {if="$servers->has($value->server) && $servers->get($value->server)->type != 'text'"}
                            <i class="material-icons" title="{$c->__('rooms.gateway_room')}">swap_horiz</i> –
                        {/if}
                        {if="$connected"}
                            {if="isset($info) && $info->description"}
                                {$info->description}
                            {else}
                                {$value->conference}
                            {/if}
                        {else}
                            <span class="second">{$value->conference}</span>
                        {/if}
                    </p>
                </li>
            {/if}
        {/loop}
    </ul>
    {if="$conferences->isEmpty()"}
    <ul class="list thick spaced">
        <li>
            <span class="primary icon green">
                <i class="material-icons">people_outline</i>
            </span>
            <p>{$c->__('rooms.empty_text1')}</p>
            <p>{$c->__('rooms.empty_text2')}</p>
        </li>
        <li>
            <span class="primary icon purple">
                <i class="material-icons">help</i>
            </span>
            <span class="control icon active" onclick="Rooms_ajaxSyncBookmark()">
                <i class="material-icons">sync</i>
            </span>
            <p>{$c->__('rooms.empty_synchronize_title')}</p>
            <p>
                {$c->__('rooms.empty_synchronize_text')}
            </p>
        </li>
    </ul>
    {/if}

    <ul class="list thin active spaced divided">
        <li onclick="Rooms_ajaxDisplay({if="$edit"}true{else}false{/if}, {if="$all"}false{else}true{/if})">
            <span class="primary icon gray">
                <i class="material-icons">
                    {if="$all"}expand_less{else}expand_more{/if}
                </i>
            </span>
            <p class="normal line">
                {if="$all"}
                    {$c->__('rooms.hide_disconnected')}
                {else}
                    {$c->__('rooms.show_all')}
                {/if}
                <span class="second">{$disconnected} <i class="material-icons">people</i></span>
            </p>
        </li>

        <li onclick="Rooms_ajaxAdd()" class="{if="$edit"}disabled{/if}"">
            <span class="primary icon gray">
                <i class="material-icons">group_add</i>
            </span>
            <p class="normal line">{$c->__('rooms.add')}</p>
        </li>
    </ul>
{else}
    {if="$c->getView() == 'room' && $room != false"}
        <div class="placeholder">
            <i class="material-icons">people</i>
            <h1>{$c->__('room.anonymous_title')}</h1>
            <h4>{$c->__('room.anonymous_login', $room)}</h4>
        </div>
        <ul class="list thick">
            <li>
                <form
                    name="loginanonymous">
                    <div>
                        <input type="text" name="nick" id="nick" required
                            placeholder="{$c->__('room.nick')}"/>
                        <label for="nick">{$c->__('room.nick')}</label>
                    </div>
                    <div>
                        <input
                            type="submit"
                            value="{$c->__('page.login')}"
                            class="button flat oppose"/>
                    </div>
                </form>
            </li>
        </ul>

        <script type="text/javascript">
            Rooms.anonymous_room = '{$room}';
        </script>
    {elseif="$c->getView() == 'room'"}
        <div class="placeholder">
            <i class="material-icons">people</i>
            <h1>{$c->__('room.anonymous_title')}</h1>
            <h4>{$c->__('room.no_room')}</h4>
        </div>
    {else}
        <div class="placeholder">
            <i class="material-icons">people</i>
            <h1>{$c->__('room.anonymous_title')}</h1>
            <h4>{$c->__('room.anonymous_text1')}</h4>
            <h4>{$c->__('room.anonymous_text2')}</h4>
        </div>
    {/if}
{/if}

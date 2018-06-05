{if="!$c->supported('anonymous') && $c->getView() != 'room'"}
    <ul class="list divided spaced middle {if="!$edit"}active{/if}">
        <li class="subheader" title="{$c->__('page.configuration')}">
            {if="$conferences->isNotEmpty()"}
            <span class="control icon active gray" onclick="Rooms_ajaxDisplay({if="$edit"}false{else}true{/if});">
                {if="$edit"}
                    <i class="material-icons">check</i>
                {else}
                    <i class="material-icons">settings</i>
                {/if}
            </span>
            {/if}
            <p>
                <span class="info">{$conferences|count}</span>
                {$c->__('chatrooms.title')}
            </p>
        </li>
        {loop="$conferences"}
            {$connected = $value->connected}
            <li {if="!$edit"} data-jid="{$value->conference}" {/if}
                {if="$value->nick != null"} data-nick="{$value->nick}" {/if}
                class="room {if="$connected"}online{/if}"
                title="{$value->conference}">
                {$url = $value->getPhoto('s')}
                {if="$url"}
                    <span class="primary
                        {if="!$connected"}disabled{/if} icon bubble color
                        {$value->name|stringToColor}"
                        style="background-image: url({$url});">
                        <span data-key="chat|{$value->conference}" class="counter"></span>
                    </span>
                {else}
                    <span class="primary
                        {if="!$connected"}disabled{/if} icon bubble color
                        {$value->name|stringToColor}">
                        <span data-key="chat|{$value->conference}" class="counter"></span>
                        {$value->name|firstLetterCapitalize}
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
                <p class="normal line">{$value->name} <span class="second">{$value->conference}</span></p>
                <p class="line"
                    {if="isset($info) && $info->description"}title="{$info->description}"{/if}>
                    {if="$connected"}
                        {$count = $value->presences()->count()}
                        <span title="{$c->__('communitydata.sub', $count)}">
                            {$count} <i class="material-icons">people</i>  –
                        </span>
                    {elseif="isset($info) && $info->occupants > 0"}
                        <span title="{$c->__('communitydata.sub', $info->occupants)}">
                            {$info->occupants} <i class="material-icons">people</i>  –
                        </span>
                    {/if}
                    {if="isset($info) && $info->description"}
                        {$info->description}
                    {else}
                        {$value->conference}
                    {/if}
                </p>
            </li>
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
    </ul>
    {/if}
{else}
    {if="$c->getView() == 'room' && $room != false"}
        <div class="placeholder icon">
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
        <div class="placeholder icon">
            <h1>{$c->__('room.anonymous_title')}</h1>
            <h4>{$c->__('room.no_room')}</h4>
        </div>
    {else}
        <div class="placeholder icon">
            <h1>{$c->__('room.anonymous_title')}</h1>
            <h4>{$c->__('room.anonymous_text1')}</h4>
            <h4>{$c->__('room.anonymous_text2')}</h4>
        </div>
    {/if}
{/if}

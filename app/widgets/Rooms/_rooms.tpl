{if="!$c->supported('anonymous') && $c->getView() != 'room'"}
    <ul class="middle divided spaced active">
        <li class="subheader">
            {$c->__('chatrooms.title')}
            <span class="info">{$conferences|count}</span>
        </li>
        {loop="$conferences"}
            {$connected = $c->checkConnected($value->conference, $value->nick)}
            <li data-jid="{$value->conference}"
                {if="$value->nick != null"} data-nick="{$value->nick}" {/if}
                class="condensed room {if="$connected"}online{/if}">
                {if="$connected"}
                    <span class="icon bubble color {$value->name|stringToColor}"><i class="zmdi zmdi-accounts"></i></span>
                {else}
                    <span class="disabled icon bubble color {$value->name|stringToColor}"><i class="zmdi zmdi-accounts-outline"></i></span>
                {/if}
                <span>{$value->name}</span>
                <p>{$value->conference}</p>
            </li>
        {/loop}

        {if="$conferences == null"}
            <li class="condensed">
                <span class="icon green">
                    <i class="zmdi zmdi-accounts-outline"></i>
                </span>
                <p>{$c->__('rooms.empty_text1')} {$c->__('rooms.empty_text2')}</p>
            </li>
        {/if}
    </ul>
{else}
    {if="$c->getView() == 'room' && $room != false"}
        <div class="placeholder icon">
            <h1>{$c->__('room.anonymous_title')}</h1>
            <h4>{$c->__('room.anonymous_login', $room)}</h4>
        </div>
        <ul class="simple divided thick">
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
                            value="{$c->__('button.come_in')}"
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

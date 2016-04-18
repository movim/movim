{if="!$c->supported('anonymous') && $c->getView() != 'room'"}
    <ul class="list divided spaced active">
        <li class="subheader">
            <p>
                <span class="info">{$conferences|count}</span>
                {$c->__('chatrooms.title')}
            </p>
        </li>
        {loop="$conferences"}
            <li data-jid="{$value->conference}"
                {if="$value->nick != null"} data-nick="{$value->nick}" {/if}
                class="room {if="$value->connected"}online{/if}">
                <span data-key="chat|{$value->conference}" class="counter"></span>
                {if="$value->connected"}
                    <span class="primary icon small bubble color {$value->name|stringToColor}"><i class="zmdi zmdi-accounts"></i></span>
                {else}
                    <span class="primary disabled icon small bubble color {$value->name|stringToColor}"><i class="zmdi zmdi-accounts-outline"></i></span>
                {/if}
                <p class="normal line">{$value->name} <span class="second">{$value->conference}</span></p>
            </li>
        {/loop}
    </ul>
    {if="$conferences == null"}
    <ul class="list thick spaced">
        <li>
            <span class="primary icon green">
                <i class="zmdi zmdi-accounts-outline"></i>
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

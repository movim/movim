{if="!$c->supported('anonymous') && $c->getView() != 'room'"}
    <ul class="list divided spaced {if="!$edit"}active{/if}">
        <li class="subheader">
            {if="$conferences != null"}
            <span class="control icon active gray" onclick="Rooms_ajaxDisplay({if="$edit"}false{else}true{/if});">
                {if="$edit"}
                    <i class="zmdi zmdi-check"></i>
                {else}
                    <i class="zmdi zmdi-settings"></i>
                {/if}
            </span>
            {/if}
            <p>
                <span class="info">{$conferences|count}</span>
                {$c->__('chatrooms.title')}
            </p>
        </li>
        {loop="$conferences"}
            <li {if="!$edit"} data-jid="{$value->conference}" {/if}
                {if="$value->nick != null"} data-nick="{$value->nick}" {/if}
                class="room {if="$value->connected"}online{/if}"
                title="{$value->conference}">
                <span data-key="chat|{$value->conference}" class="counter"></span>
                <span class="primary {if="!$value->connected"}disabled{/if} icon small bubble color {$value->name|stringToColor}">
                    {$value->name|firstLetterCapitalize:true}
                </span>
                {if="$edit"}
                    <span class="control icon active gray" onclick="Rooms_ajaxRemoveConfirm('{$value->conference}');">
                        <i class="zmdi zmdi-delete"></i>
                    </span>
                    <span class="control icon active gray" onclick="Rooms_ajaxEdit('{$value->conference}');">
                        <i class="zmdi zmdi-edit"></i>
                    </span>
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

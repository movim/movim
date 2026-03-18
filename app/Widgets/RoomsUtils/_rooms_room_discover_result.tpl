{if="$info->isMuc()"}
    <ul class="list thick flex card">
        <li class="block large">
            <div>
                <p class="line">
                    {if="$info->occupants"}
                        <span class="info">{$info->occupants} <i class="material-symbols">people</i></span>
                    {/if}
                    {$info->name ?? ''}
                    <span class="second">
                        {if="$info->isGroupChat()"}
                            <i class="material-symbols">people_alt</i> {$c->__('room.group_chat')}
                        {else}
                            <i class="material-symbols">wifi_tethering</i> {$c->__('room.channel')}
                        {/if}
                    </span>
                </p>
                <p>{$info->description ?? ''}</p>
                <p></p>
            </div>
        </li>
    </ul>

    {if="$info->uuidMuc"}
        <ul class="list middle">
            <li>
                <span class="primary icon blue">
                    <i class="material-symbols">info</i>
                </span>
                <div>
                    <p></p>
                    <p>{$c->__('rooms.disco_maybe_space')}</p>
                </div>
            </li>
        </ul>
    {/if}
{else}
    <ul class="list">
        <li>
            <span class="primary icon red">
                <i class="material-symbols">error</i>
            </span>
            <div>
                <p></p>
                <p>{$c->__('rooms.disco_not_muc')}</p>
            </div>
        </li>
    </ul>
{/if}

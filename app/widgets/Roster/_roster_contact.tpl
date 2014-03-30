<li
    id="{$jid}"
    title="{$jid}_{$name}"
    class="{$presence}">
    <ul class="contact">
        {loop="$contact"}
            <li
                title="{$value.jid}{if="$value.status != ''"} - {$value.status}{/if}"
                class="{$value.presencetxt} {$value.inactive} {if="$value.client"}client {$value.client}{/if}">
                <div
                    class="chat on"
                    onclick="{$value.openchat}">
                </div>

                {if="$value.type == 'handheld'"}
                <div class="infoicon mobile"></div>
                {/if}
                
                {if="$value.type == 'web'"}
                <div class="infoicon web"></div>
                {/if}
                
                {if="$value.type == 'bot'"}
                <div class="infoicon bot"></div>
                {/if}
                
                {if="$value.tune"}
                <div class="infoicon tune"></div>
                {/if}
                
                {if="$value.jingle"}
                <div
                    class="infoicon jingle"
                    onclick="Popup.close(); Popup.open('{$value.jid}/{$value.ressource}')">
                </div>
                {/if}

                
                <a href="{$c->route('friend', $value.jid)}">
                    <img
                        class="avatar"
                        src="{$value.avatar}"
                        alt="avatar"
                    />{$value.name}
                    <span class="ressource">{$value.ressource}</span>
                </a>
            </li>
        {/loop}
    </ul>
</li>

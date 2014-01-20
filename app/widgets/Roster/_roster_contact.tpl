<li id="{$jid}">
    <ul class="contact">
        {loop="contact"}
            <li
                title="{$value.jid}{if="$value.status != ''"} - {$value.status}{/if}"
                class="{$value.presencetxt} {$value.inactive}">
                <div
                    class="chat on"
                    onclick="{$value.openchat}">
                </div>
                <a href="{$c->route('friend', $value.jid)}">
                    <img
                        class="avatar"
                        src="{$value.avatar}"
                    />{$value.name}
                    <span class="ressource">{$value.ressource}</span>
                </a>
            </li>
        {/loop}
    </ul>
</li>

<section class="scroll">
    <h3>{$c->__('chatrooms.users')}</h3>
    <br />
    <ul class="list middle">
        {$presence = getPresencesTxt()}
        {loop="$list"}
            <li class="{if="$value->last > 60"} inactive{/if}"
                title="{$value->resource}">
                {$url = $value->getPhoto('s')}
                {if="$url"}
                    <span class="primary icon bubble status {$presence[$value->value]}">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->resource|stringToColor} status {$presence[$value->value]}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}
                {if="$value->mucaffiliation =='owner'"}
                    <div class="control">
                        <i class="zmdi zmdi-beenhere"></i>
                    </div>
                {/if}
                {if="$value->mucjid && strpos($value->mucjid, '/') == false && !$c->supported('anonymous')"}
                    <p class="line normal">
                        <a href="{$c->route('contact', $value->mucjid)}">{$value->resource}</a>
                    </p>
                {else}
                    <p class="line normal">{$value->resource}</p>
                {/if}
                {if="$value->status"}
                    <p class="line">{$value->status}</p>
                {/if}
            </li>
        {/loop}
    </ul>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>

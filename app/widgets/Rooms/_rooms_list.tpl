<section class="scroll">
    <h3>{$c->__('chatrooms.users')}</h3>
    <br />
    <ul>
        {$presence = getPresencesTxt()}
        {loop="$list"}
            <li class="
                    action
                    {if="$value->last > 60"} inactive{/if}
                    {if="$value->status"}condensed{/if}"
                title="{$value->resource}">
                {$url = $value->getPhoto('s')}
                {if="$url"}
                    <span class="icon bubble status {$presence[$value->value]}">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="icon bubble color {$value->resource|stringToColor} status {$presence[$value->value]}">
                        <i class="zmdi zmdi-account"></i>
                    </span>        
                {/if}
                {if="$value->mucaffiliation =='owner'"}
                    <div class="action">
                        <i class="zmdi zmdi-beenhere"></i>
                    </div>
                {/if}
                {if="$value->mucjid && strpos($value->mucjid, '/') == false && !$c->supported('anonymous')"}
                    <a href="{$c->route('contact', $value->mucjid)}">
                        <span>{$value->resource}</span>
                    </a>
                {else}
                    <span>{$value->resource}</span>
                {/if}
                {if="$value->status"}
                    <p class="wrap">{$value->status}</p>
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

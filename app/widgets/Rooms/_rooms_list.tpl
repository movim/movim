<section class="scroll">
    <h3>{$c->__('chatrooms.users')}</h3>
    <br />
    <ul>
        {$presence = getPresencesTxt()}
        {loop="$list"}
            <li>
                <span class="icon bubble color {$value->resource|stringToColor} status {$presence[$value->value]}">
                    {$value->resource|firstLetterCapitalize}
                </span>
                {if="$value->mucaffiliation =='owner'"}
                    <div class="control">
                        <i class="md md-beenhere"></i>
                    </div>
                {/if}
                <span>{$value->resource}</span>
            </li>
        {/loop}
    </ul>
</section>
<div class="no_bar">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.cancel')}
    </a>
</div>

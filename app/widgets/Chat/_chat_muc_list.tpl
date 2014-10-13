<ul>
{loop="$muclist"}
    {if="$value->value < 5"}
        <li class="{$c->colorNameMuc($value->ressource)}" title="{$value->status}">
            {if="$value->mucaffiliation == 'owner'"}
                <i class="fa fa-flag"></i> 
            {/if}
            {$value->ressource}
        </li>
    {/if}
{/loop}
</ul>

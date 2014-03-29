<ul>
{loop="$muclist"}
    {if="$value->value < 5"}
        <li class="{$c->colorNameMuc($value->ressource)}">
            {$value->ressource}
        </li>
    {/if}
{/loop}
</ul>

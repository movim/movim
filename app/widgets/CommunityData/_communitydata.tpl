<br />
{if="$info"}
    {if="$info->logo"}
        <li class="block large">
            <p class="center">
                <img src="{$info->getLogo()}" style="max-width: 100%"/>
            </p>
        </li>
    {/if}
    <li class="block large">
        <p>{$info->name}</p>
        <p>
            {if="$info->created"}
                {$info->created|strtotime|prepareDate:true,true}
            {/if}
            {if="$info->num > 0"}
                 – {$c->__('communitydata.num', $info->num)}
            {/if}
            {if="$info->occupants > 0"}
                <br />{$c->__('communitydata.sub', $info->occupants)}
            {/if}
        </p>
    </li>
{/if}

<ul class="list block middle active">
    <a href="{$c->route('node', [$info->server, $info->node])}" target="_blank" class="block">
        <li>
            <span class="primary icon">
                <i class="zmdi zmdi-portable-wifi"></i>
            </span>
            <span class="control icon">
                <i class="zmdi zmdi-chevron-right"></i>
            </span>
            <p class="normal">{$c->__('communitydata.public')}</p>
        </li>
    </a>
</ul>

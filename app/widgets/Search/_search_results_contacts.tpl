<ul class="list flex">
    <li class="subheader block large">
        <div>
            <p>{$c->__('explore.explore')}</p>
        </div>
    </li>
    {loop="$users"}
        <li class="block active" title="{$value->jid}" onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}')">
            {$url = $value->getPhoto('m')}
            {if="$url"}
                <span class="primary icon bubble
                {if="$value->value"}
                    status {$presencestxt[$value->value]}
                {/if}
                " style="background-image: url({$url});">
                </span>
            {else}
                <span class="primary icon bubble color {$value->jid|stringToColor}
                {if="$value->value"}
                    status {$presencestxt[$value->value]}
                {/if}
                ">
                    <i class="material-icons">person</i>
                </span>
            {/if}

            <div>
                <p class="normal line">
                    {$value->truename}
                </p>
                {if="!empty($value->description)"}
                    <p class="line" title="{$value->description|strip_tags}">
                        {$value->description|strip_tags|truncate:80}
                    </p>
                {elseif="$value->value"}
                    <p class="line">{$presences[$value->value]}</p>
                {/if}
            </div>
        </li>
    {/loop}
</ul>
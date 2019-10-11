<ul class="list flex active">
    <li class="subheader block large">
        <p>{$c->__('explore.explore')}</p>
    </li>
    {loop="$users"}
        <li class="block" title="{$value->jid}" onclick="MovimUtils.redirect('{$c->route('contact', $value->jid)}')">
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

            <span class="control icon active gray" onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}')">
                <i class="material-icons">person</i>
            </span>
            <span class="control icon active gray" onclick="Search.chat('{$value->jid|echapJS}')">
                <i class="material-icons">comment</i>
            </span>

            <p class="normal line">
                {$value->truename}
                {if="!empty($value->description)"}
                    <span class="second" title="{$value->description|strip_tags}">
                        {$value->description|strip_tags|truncate:80}
                    </span>
                {/if}
            </p>
        </li>
    {/loop}
</ul>
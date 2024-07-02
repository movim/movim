<hr />
<ul class="list flex large">
    <li class="subheader block large">
        <div>
            <p>{$c->__('explore.explore')}</p>
        </div>
    </li>
    {loop="$users"}
        <li class="block active" title="{$value->jid}" onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}'); Drawer.clear();">
            <span class="primary icon bubble {if="$value->value"}status {$presencestxt[$value->value]}{/if}">
                <img src="{$value->getPicture()}">
            </span>
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

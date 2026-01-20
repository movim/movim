<hr />
<ul class="list">
    <li class="subheader">
        <div>
            <p>{$c->__('explore.explore')}</p>
        </div>
    </li>
</ul>
<ul class="list flex fourth card shadow compact middle active">
    {loop="$users"}
        <li class="block active" title="{$value->id}" onclick="MovimUtils.reload('{$c->route('contact', $value->id)}'); Drawer.clear();">
            <img class="main" src="{$value->getBanner(\Movim\ImageSize::L)}">
            <span class="primary icon bubble {if="$value->value"}status {$presencestxt[$value->value]}{/if}">
                <img src="{$value->getPicture()}">
            </span>
            <div>
                <p class="line">
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

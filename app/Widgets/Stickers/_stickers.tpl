<section id="stickers" class="scroll">
    <ul class="list flex fourth">
        {loop="$pack->stickers"}
            <li class="block" onclick="Stickers.zoom(this, '{$jid}', {$value->id});">
                <img class="sticker" src="{$value->url}"/>
            </li>
        {/loop}
    </ul>
    <ul class="list">
        <li class="block large">
            <span class="primary icon gray">
                <i class="material-symbols">person</i>
            </span>
            <div>
                <p class="line">
                    {if="!empty($pack->url)"}
                        <a href="{$pack->url}" target="_blank">{$pack->author}</a>
                    {else}
                        {$pack->author}
                    {/if}
                </p>
                <p class="line">Under {$pack->license}</p>
            </div>
        </li>
    </ul>
</section>
<div>
    <ul class="tabs narrow">
        {if="$gifEnabled"}
            <li onclick="Stickers_ajaxShow('{$jid}')">
                <i class="material-symbols" style="font-size: 5rem;">gif</i>
            </li>
        {/if}
        {loop="$packs"}
            <li onclick="Stickers_ajaxShow('{$jid}', '{$value->name}')" {if="$value->name == $pack->name"}class="active"{/if}>
                <img alt=":sticker:" class="emoji medium" src="{$value->stickers()->first()->url}">
            </li>
        {/loop}
    </ul>
</div>

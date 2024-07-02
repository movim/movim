<section id="stickers" class="scroll">
    <ul class="list flex fourth">
        {loop="$stickers"}
            {if="strlen($value) == 44"}
            <li class="block" onclick="Stickers.zoom(this, '{$jid}', '{$pack}', '{$value}');">
                <img class="sticker" src="{$c->baseUri}stickers/{$pack}/{$value}"/>
            </li>
            {/if}
        {/loop}
    </ul>
    <ul class="list">
        <li class="block large">
            <span class="primary icon gray">
                <i class="material-symbols">person</i>
            </span>
            <div>
                <p class="line">
                    {if="!empty($info.url)"}
                        <a href="{$info.url}" target="_blank">{$info.author}</a>
                    {else}
                        {$info.author}
                    {/if}
                </p>
                <p class="line">Under {$info.license}</p>
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
            <li onclick="Stickers_ajaxShow('{$jid}', '{$value}')" {if="$value == $pack"}class="active"{/if}>
                <img alt=":sticker:" class="emoji medium" src="{$c->baseUri}stickers/{$value}/icon.png">
            </li>
        {/loop}
    </ul>
</div>

<section id="stickers" class="scroll">
    <ul class="list flex quarter">
        {loop="$stickers"}
            {if="strlen($value) == 44"}
            <li class="block" onclick="Stickers.zoom(this, '{$jid}', '{$pack}', '{$value}');">
                <img class="sticker" src="/stickers/{$pack}/{$value}"/>
            </li>
            {/if}
        {/loop}
    </ul>
    <ul class="list">
        <li class="block large">
            <span class="primary icon gray">
                <i class="material-icons">person</i>
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
        {loop="$packs"}
            <li onclick="Stickers_ajaxShow('{$jid}', '{$value}')" {if="$value == $pack"}class="active"{/if}>
                <a href="#"><img alt=":sticker:" class="emoji medium" src="/stickers/{$value}/icon.png"></a>
            </li>
        {/loop}
        <li onclick="Stickers_ajaxSmiley('{$jid}')">
            <a href="#"><img alt=":smiley:" class="emoji medium" src="{$c->getSmileyPath('1f603')}"></a>
        </li>
    </ul>
</div>

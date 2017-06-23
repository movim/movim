<section id="stickers" class="scroll">
    <ul class="list flex">
        {loop="$stickers"}
            {if="strlen($value) == 44"}
            <li class="block" onclick="Stickers.zoom(this, '{$jid}', '{$pack}', '{$value}');">
                <img class="sticker" src="{$path}/{$pack}/{$value}"/>
            </li>
            {/if}
        {/loop}
        <li class="block large">
            <span class="primary icon gray">
                <i class="zmdi zmdi-account"></i>
            </span>
            <p class="line">
                {if="!empty($info.url)"}
                    <a href="{$info.url}" target="_blank">{$info.author}</a>
                {else}
                    {$info.author}
                {/if}
            </p>
            <p class="line">Under {$info.license}</p>
        </li>
    </ul>
</section>
<div>
    <ul class="tabs narrow">
        {loop="$packs"}
            <li onclick="Stickers_ajaxShow('{$jid}', '{$value}')" {if="$value == $pack"}class="active"{/if}>
                <a href="#"><img alt=":sticker:" class="emoji medium" src="{$path}/{$value}/icon.png"></a>
            </li>
        {/loop}
        <li onclick="Stickers_ajaxSmiley('{$jid}')">
            <a href="#"><img alt=":smiley:" class="emoji medium" src="{$c->getSmileyPath('1f603')}"></a>
        </li>
        <li onclick="Stickers_ajaxSmileyTwo('{$jid}')">
            <a href="#"><img alt=":smiley:" class="emoji medium" src="{$c->getSmileyPath('1f44d')}"></a>
        </li>
    </ul>
</div>

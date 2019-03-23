<section>
    {autoescape="off"}
        {$emojis}
    {/autoescape}
</section>
<div>
    <ul class="tabs narrow">
        {loop="$packs"}
            <li onclick="Stickers_ajaxShow('{$jid}', '{$value}')">
                <a href="#"><img alt=":sticker:" class="emoji medium" src="{$path}/{$value}/icon.png"></a>
            </li>
        {/loop}
        <li onclick="Stickers_ajaxSmiley('{$jid}')" class="active">
            <a href="#"><img alt=":smiley:" class="emoji medium" src="{$c->getSmileyPath('1f603')}"></a>
        </li>
    </ul>
</div>

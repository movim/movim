<section>
    {autoescape="off"}
        {$emojis}
    {/autoescape}
</section>
<div>
    <ul class="tabs narrow">
        {loop="$packs"}
            <li onclick="Stickers_ajaxShow('{$jid}', '{$value}')">
                <a href="#"><img alt=":sticker:" class="emoji medium" src="/stickers/{$value}/icon.png"></a>
            </li>
        {/loop}
    </ul>
</div>

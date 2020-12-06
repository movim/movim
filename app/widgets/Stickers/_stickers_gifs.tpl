<section id="gifs" class="scroll">
    <div class="masonry first"></div>
    <div class="masonry second"></div>
    <div class="placeholder">
        <i class="material-icons">search</i>
        <h1>{$c->__('sticker.gif_title')}</h1>
        <h4>{$c->__('sticker.gif_text')}</h4>
    </div>
</section>
<div>
    <ul id="gifssearchbar" class="list fill thin">
        <li>
            <span class="primary icon gray">
                <i class="material-icons">search</i>
            </span>
            <form name="search" onsubmit="return false;">
                <div>
                    <input name="keyword" autocomplete="off"
                        title="{$c->__('sticker.keyword')}"
                        placeholder="{$c->__('sticker.keyword')}"
                        type="text">
                </div>
            </form>
        </li>
    </ul>
    <ul class="tabs narrow">
        <li onclick="Stickers_ajaxShow('{$jid}')" class="active">
            <a href="#"><i class="material-icons" style="font-size: 5rem;">gif</i></a>
        </li>
        {loop="$packs"}
            <li onclick="Stickers_ajaxShow('{$jid}', '{$value}')">
                <a href="#"><img alt=":sticker:" class="emoji medium" src="/stickers/{$value}/icon.png"></a>
            </li>
        {/loop}
    </ul>
</div>

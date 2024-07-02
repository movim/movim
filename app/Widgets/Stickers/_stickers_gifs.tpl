<section id="gifs" class="scroll">
    <div class="masonry first"></div>
    <div class="masonry second"></div>
    <div class="placeholder">
        <i class="material-symbols">search</i>
        <h1>{$c->__('sticker.gif_title')}</h1>
        <h4>{$c->__('sticker.gif_text')}</h4>
    </div>
</section>
<div>
    <ul id="gifssearchbar" class="list fill">
        <li class="search">
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
            <i class="material-symbols" style="font-size: 5rem;">gif</i>
        </li>
        {loop="$packs"}
            <li onclick="Stickers_ajaxShow('{$jid}', '{$value}')">
                <img alt=":sticker:" class="emoji medium" src="{$c->baseUri}stickers/{$value}/icon.png">
            </li>
        {/loop}
    </ul>
</div>

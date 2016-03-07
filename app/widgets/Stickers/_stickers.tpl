<section class="scroll">
    <ul class="tabs">
        <li onclick="Stickers_ajaxShow('{$jid}')" class="active">
            <a href="#"><img alt=":sticker:" class="emoji medium" src="{$icon}"></a>
        </li>
        <li onclick="Stickers_ajaxSmiley('{$jid}')">
            <a href="#"><img alt=":smiley:" class="emoji medium" src="{$c->getSmileyPath('1f603')}"></a>
        </li>
    </ul>
    <ul class="list flex third active">
        {loop="$stickers"}
            <li class="block" onclick="Stickers_ajaxSend('{$jid}', '{$value}'); Dialog.clear();">
                <img class="sticker" src="{$path}{$value}"/>
            </li>
        {/loop}
        <li class="block large">
            <span class="primary icon gray">
                <i class="zmdi zmdi-account"></i>
            </span>
            <p class="line">Corine Tea</p>
            <p class="line">Under CreativeCommon BY NC SA</p>
        </li>
    </ul>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>

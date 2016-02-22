<section class="scroll">
    <ul class="list flex active">
        {loop="$stickers"}
            <li class="block" onclick="Stickers_ajaxSend('{$jid}', '{$value}'); Dialog.clear();">
                <img src="{$path}{$value}"/>
            </li>
        {/loop}
    </ul>
</section>
<div>
    <a onclick="Chat_ajaxSmiley()" class="button flat">
        Emojis
    </a>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>

<section class="scroll">
    <h3>{$c->__('chats.add')}</h3>
    <ul class="active" id="add_extend">
        <li class="subheader condensed">{$c->__('chats.frequent')}</li>
        {loop="$top"}
            <li class="condensed" onclick="Chats_ajaxOpen('{$value->jid}'); Dialog.clear()">
                <span class="icon bubble">
                    <img
                        class="avatar"
                        src="{$value->getPhoto('s')}"
                        alt="avatar"
                    />
                </span>
                <span>{$value->getTrueName()}</span>
                <p class="wrap">{$value->jid}</p>
            </li>
        {/loop}
        <li onclick="Chats_ajaxAddExtend()">
            <span class="icon">
                <i class="md md-add"></i>
            </span>
            <span>{$c->__('chats.more')}</span>
        </li>
    </ul>
    <br />
    <!--<div id="search_results">

    </div>-->
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>

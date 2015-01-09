<section class="scroll">
    <h3>{$c->__('chats.add')}</h3>
    <br />
    <ul class="active">
        {loop="$contacts"}
            {if="$group != $value->groupname"}
                <li class="subheader">{$value->groupname}</li>
            {/if}
            <li onclick="Chats_ajaxOpen('{$value->jid}'); Dialog.clear()">
                <span class="icon bubble">
                    <img
                        class="avatar"
                        src="{$value->getPhoto('s')}"
                        alt="avatar"
                    />
                </span>
                <span>{$value->getTrueName()}</span>
            </li>
            {$group = $value->groupname}
        {/loop}
    </ul>
    <!--<div id="search_results">

    </div>-->
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>

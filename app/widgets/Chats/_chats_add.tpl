<section class="scroll">
    <h3>{$c->__('chats.add')}</h3>
    <ul class="list active" id="add_extend">
        <li class="subheader">
            <p>{$c->__('chats.frequent')}</p>
        </li>
        {loop="$top"}
            {if="!in_array($value->jid, $chats)"}
                <li class="{if="$value->last > 60"} inactive{/if}"
                        onclick="Chats_ajaxOpen('{$value->jid}'); Dialog.clear()">
                        {$url = $value->getPhoto('s')}
                        {if="$url"}
                            <span class="primary icon bubble
                                {if="$value->value"}
                                    status {$presencestxt[$value->value]}
                                {/if}">
                                <img src="{$url}">
                            </span>
                        {else}
                            <span class="primary icon bubble color {$value->jid|stringToColor}
                                {if="$value->value"}
                                    status {$presencestxt[$value->value]}
                                {/if}">
                                <i class="zmdi zmdi-account"></i>
                            </span>
                        {/if}
                        <p class="line">{$value->getTrueName()}</p>
                        <p class="line">{$value->jid}</p>
                    </li>
            {/if}
        {/loop}
        <li onclick="Chats_ajaxAddExtend()">
            <span class="primary icon">
                <i class="zmdi zmdi-plus"></i>
            </span>
            <p class="normal">{$c->__('chats.more')}</p>
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

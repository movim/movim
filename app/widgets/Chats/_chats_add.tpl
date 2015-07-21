<section class="scroll">
    <h3>{$c->__('chats.add')}</h3>
    <ul class="active" id="add_extend">
        <li class="subheader">{$c->__('chats.frequent')}</li>
        {loop="$top"}
            {if="!in_array($value->jid, $chats)"}
                <li class="condensed {if="$value->last > 60"} inactive{/if}"
                        onclick="Chats_ajaxOpen('{$value->jid}'); Dialog.clear()">
                        {$url = $value->getPhoto('s')}
                        {if="$url"}
                            <span class="icon bubble
                                {if="$value->value"}
                                    status {$presencestxt[$value->value]}
                                {/if}">
                                <img src="{$url}">
                            </span>
                        {else}
                            <span class="icon bubble color {$value->jid|stringToColor}
                                {if="$value->value"}
                                    status {$presencestxt[$value->value]}
                                {/if}">
                                <i class="zmdi zmdi-account"></i>
                            </span>
                        {/if}
                        <span>{$value->getTrueName()}</span>
                        <p class="wrap">{$value->jid}</p>
                    </li>
            {/if}
        {/loop}
        <li onclick="Chats_ajaxAddExtend()">
            <span class="icon">
                <i class="zmdi zmdi-plus"></i>
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

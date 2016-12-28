<section>
    <h3>{$c->__('communityheader.sure')}</h3>
    {if="$item"}
        <br />
        <h4 class="gray">
            {$c->__('communityheader.unsubscribe_text')} : {$item->name}
        </h4>
    {/if}
</section>
<div>
    <a onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </a>
    <a
        onclick="CommunityHeader_ajaxUnsubscribe('{$server|echapJS}', '{$node|echapJS}'); Dialog_ajaxClear()"
        class="button flat">
        {$c->__('communityheader.unsubscribe')}
    </a>
</div>

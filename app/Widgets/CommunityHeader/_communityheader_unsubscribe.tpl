<section>
    <h3>{$c->__('communityheader.sure')}</h3>
    {if="$info"}
        <br />
        <h4 class="gray">
            {$c->__('communityheader.unsubscribe_text')}: {$info->name}
        </h4>
    {/if}
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        onclick="CommunityHeader_ajaxUnsubscribe('{$server|echapJS}', '{$node|echapJS}'); Dialog_ajaxClear()"
        class="button flat">
        {$c->__('communityheader.unsubscribe')}
    </button>
</div>

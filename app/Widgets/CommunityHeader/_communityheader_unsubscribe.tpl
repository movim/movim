<section>
    <h3>{$c->__('communityheader.sure')}</h3>
    {if="$info"}
        <br />
        <h4 class="gray">
            {$c->__('communityheader.unfollow_text')}: {$info->name}
        </h4>
    {/if}
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        onclick="CommunityHeader_ajaxUnsubscribe('{$server|echapJS}', '{$node|echapJS}'); Dialog_ajaxClear()"
        class="button flat">
        {$c->__('communityheader.unfollow')}
    </button>
</footer>

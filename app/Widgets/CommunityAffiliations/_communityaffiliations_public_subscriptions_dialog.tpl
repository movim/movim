<section class="scroll">
    {if="$subscriptions->isNotEmpty()"}
        {autoescape="off"}
            {$c->preparePublicSubscriptionsList($subscriptions)}
        {/autoescape}
    {/if}
</section>
<div>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</div>
